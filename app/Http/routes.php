<?php
/*
 * A simple contact manager app.  This app follows a RESTful-like API in
 * that is will use proper HTTP GET, POST, PUT, and DELETE requests, and
 * then return simple JSON strings.
 *
 * This app is verbose and may not follow all of the best coding practices
 * as this is for learning and demonstration purposes only.
 *
 * Created by Eric Jones (https://github.com/erj1) for use in an
 * Indy Dev Workshop Meetup regarding Lumen, and building a simple API with
 * Lumen.
 */

use Carbon\Carbon;
use Illuminate\Http\Request;

/*
 * These are all of the rules that are related to the completion of the
 * the various forms related to a contact.
 *
 * This should probably be in a better place (i.e., outside of the routing
 * file) but I have left it in here for easier access when discussing the rest
 * of the code in this file.
 */
$rules = [
    'contact' => [
        'first_name'     => 'required|max:255',
        'last_name'      => 'required|max:255',
        'birthday_month' => 'integer|min:1|max:12',
        'birthday_year'  => 'integer|min:1900|max:' . date('Y'),
        'birthday_day'   => 'integer|min:1|max:31'
    ],
    'address' => [
        'address_1' => 'required|max:255',
        'address_2' => 'max:255',
        'city'      => 'required|max:255',
        'state'     => 'required|max:255',
        'zip'       => 'required|max:255',
        'country'   => 'max:255'
    ],
    'phone' => [
        'phone' => 'required|max:100'
    ],
    'email' => [
        'email' => 'required|max:255|email'
    ]
];

/*
 * Welcome.
 * I didn't change the default Lumen welcome screen.
 */
$app->get('/', function() use ($app) {
    return $app->welcome();
});

/*
 * All Contacts
 * Shows all contacts in the application.
 * Note: Below are items that can be considered / implemented when expanding
 * out this app.
 *  - Add some pagination
 *  - Add some way to get all details for all contacts
 */
$app->get('contacts', function() {
    $contacts = DB::table('contacts')->get();
    return response()->json($contacts);
});

/*
 * A simple redirection to the main contacts URL
 */
$app->get('contact', function() {
    return redirect('/contacts');
});

/*
 * Create A Contact
 * Create a new contact within the app.
 */
$app->post('contact', function (Request $request) use ($rules) {

    // only use the data that we are expecting.
    $input = $request->only([
        'first_name', 'last_name', 'birthday_month', 'birthday_day', 'birthday_year'
    ]);

    // validate that data
    $validator = Validator::make($input, $rules['contact']);
    if ($validator->fails()) {
        return response($validator->errors(), 400);
    }

    // insert the validated data into the database
    DB::table('contacts')->insert([
        'first_name'     => $input['first_name'],
        'last_name'      => $input['last_name'],
        'birthday_month' => (int) $input['birthday_month'],
        'birthday_day'   => (int) $input['birthday_day'],
        'birthday_year'  => (int) $input['birthday_year'],
        'created_at'     => Carbon::now(),
        'updated_at'     => Carbon::now()
    ]);

    // return a simple 201 (created)
    return response('Created', 201);
});

/*
 * Get A Contact
 * Provides a single contact record
 * @todo Check for a full / partial data flag.
 */
$app->get('contact/{contact_id}', function($contact_id) {

    // pull the contact from the DB
    $contact = DB::table('contacts')
        ->where('id', (int) $contact_id)
        ->first();

    // check for the existance of the contact
    if (!$contact) {
        return response('Not Found', 404);
    }

    // return the contact record
    return response()->json($contact);
});

/*
 * Update A Contact's Information
 */
$app->put('contact/{contact_id}',
    function (Request $request, $contact_id) use ($rules) {

        // get the contact from the database
        $contact = DB::table('contacts')
            ->where('id', (int) $contact_id)
            ->first();

        // check for the existance of the contact
        if (!$contact) {
            return response('Not Found', 404);
        }

        // only get data we need from the request
        $input = $request->only([
            'first_name', 'last_name', 'birthday_month', 'birthday_day', 'birthday_year'
        ]);

        // validate the incoming request data
        $validator = Validator::make($input, $rules['contact']);
        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        // update the contact record in the database
        // Note this code uses the Laravel function array_get()
        // which will fill in blank input values with the
        // pre-existing contact values.
        DB::table('contacts')
            ->where('id', (int) $contact_id)
            ->update([
                'first_name'     => array_get($input, 'first_name', $contact->first_name),
                'last_name'      => array_get($input, 'last_name', $contact->last_name),
                'birthday_month' => (int) array_get($input, 'birthday_month', $contact->birthday_month),
                'birthday_day'   => (int) array_get($input, 'birthday_day', $contact->birthday_day),
                'birthday_year'  => (int) array_get($input, 'birthday_year', $contact->birthday_year),
                'updated_at'     => Carbon::now()
            ]);

        // inform the requester that the contact has been updated
        return response('Updated', 200);
    });

// delete a contact
/*
 * Delete A Contact
 * Note: if you delete a contact all other associated records
 * will be deleted too.
 */
$app->delete('contact/{contact_id}/', function ($contact_id) {

    // get the contact from the DB
    $contact = DB::table('contacts')
        ->where('id', (int) $contact_id)
        ->first();

    // check for the existense of a contact record
    if (!$contact) {
        return response('Not Found', 404);
    }

    // delete the associated contact records or else there
    // will be orphan records ( an address without a contact )

    DB::table('addresses')
        ->where('contact_id', (int) $contact->id)
        ->delete();

    DB::table('phones')
        ->where('contact_id', (int) $contact->id)
        ->delete();

    DB::table('emails')
        ->where('contact_id', (int) $contact->id)
        ->delete();

    // ... and finally delete the contact

    DB::table('contacts')
        ->where('id', (int) $contact->id)
        ->delete();

    return response('Deleted', 200);
});

/*
 * ============================================================================
 * The following routes should be scoped to a specific contact account.
 * ============================================================================
 */

/*
 * ----------------------------------------------------------------------------
 * Address Routes
 * ----------------------------------------------------------------------------
 */

/*
 * Contact's Addresses
 */
$app->get('contact/{contact_id}/address', function ($contact_id) {

    // pull the contact from the database
    $contact = DB::table('contacts')
        ->where('id', (int) $contact_id)
        ->first();

    // pull all the associated address information for that contact
    $addresses = DB::table('addresses')
        ->where('contact_id', (int) $contact->id)
        ->get();

    // return only the address information
    return response()->json($addresses);
});


/*
 * Add An Address To A Contact
 */
$app->post('contact/{contact_id}/address',
    function (Request $request, $contact_id) use ($rules) {

        // pull the contact record from the database
        $contact = DB::table('contacts')
            ->where('id', $contact_id)
            ->first();

        // make sure that we have a contact
        if (!$contact) {
            return response('Not Found', 404);
        }

        // only get the input we want
        $input = $request->only([
            'address_1', 'address_2', 'city', 'state', 'zip', 'country',
        ]);

        // validate the incoming data
        $validator = Validator::make($input, $rules['address']);
        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        // create the address record
        // Note if 'address_2' is null then use a empty string
        DB::table('addresses')->insert([
            'contact_id' => (int) $contact->id,
            'address_1'  => $input['address_1'],
            'address_2'  => $input['address_2'],
            'city'       => $input['city'],
            'state'      => $input['state'],
            'zip'        => $input['zip'],
            'country'    => $input['country']
        ]);

        // return a created response
        return response('Created', 201);
    });

/*
 * Get An Address For A Contact
 */
$app->get('contact/{contact_id}/address/{address_id}',
    function ($contact_id, $address_id) {

        // get the contact address
        $address = DB::table('addresses')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $address_id)
            ->get();

        // check for the existence of the address
        if (!$address) {
            return response('Not Found', 404);
        }

        // return the address
        return response()->json($address);
    });

/*
 * Update An Address For A Contact
 */
$app->put('contact/{contact_id}/address/{address_id}',
    function (Request $request, $contact_id, $address_id) use ($rules) {

        // get the contact address
        $address = DB::table('addresses')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $address_id)
            ->first();

        // check if the address exists
        if (!$address) {
            return response('Contact Address Record Not Found', 404);
        }

        // only get the input we want
        $input = $request->only([
            'address_1', 'address_2', 'city', 'state', 'zip', 'country',
        ]);

        // validate the incoming request
        $validator = Validator::make($input, $rules['address']);
        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        // update the address record
        DB::table('addresses')
            ->where('id', $address->id)
            ->update([
                'address_1'  => $input['address_1'],
                'address_2'  => $input['address_2'],
                'city'       => $input['city'],
                'state'      => $input['state'],
                'zip'        => $input['zip'],
                'country'    => $input['country']
            ]);

        // notify
        return response('Updated', 200);
    });

/*
 * Delete An Address
 */
$app->delete('contact/{contact_id}/address/{address}',
    function ($contact_id, $address_id) {

        // pull the address from the DB
        $address = DB::table('addresses')
            ->where('contact_id', $contact_id)
            ->where('id', $address_id)
            ->first();

        // make sure the address exists
        if (!$address) {
            return response('Not Found', 404);
        }

        // delete the address
        DB::table('addresses')
            ->where('id', (int) $address->id)
            ->delete();

        // notify
        return response('Deleted', 200);
    });

/*
 * ----------------------------------------------------------------------------
 * Contact Phone Routes
 * ----------------------------------------------------------------------------
 */

/*
 * Get the phones for a contact
 */
$app->get('contact/{contact_id}/phone', function ($contact_id) {

    // pull the contact from the DB
    $contact = DB::table('contacts')
        ->where('id', (int) $contact_id)
        ->first();

    // check it exists
    if (!$contact) {
        return response('Not Found', 404);
    }

    // pull the phones from the
    $phones = DB::table('phones')
        ->where('contact_id', (int) $contact->id)
        ->get();

    // return the phones data
    return response()->json($phones);
});

/*
 * Create a phone for a specific contact
 */
$app->post('contact/{contact_id}/phone',
    function (Request $request, $contact_id) use ($rules) {

        // pull the contact
        $contact = DB::table('contacts')
            ->where('id', (int) $contact_id)
            ->first();

        // make sure the contact exists
        if (!$contact) {
            return response('Not Found', 404);
        }

        // only the the input we care about
        $input = $request->only(['phone']);

        // validate the incoming data
        $validator = Validator::make($input, $rules['phone']);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        // insert the new phone into the DB
        DB::table('phones')
            ->insert([
                'contact_id' => (int) $contact->id,
                'phone' => $input['phone']
            ]);

        // notify the requestor
        return response('Created', 201);
    });

// get a single phone
$app->get('contact/{contact_id}/phone/{phone_id}',
    function ($contact_id, $phone_id) {

        $phone = DB::table('phones')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $phone_id)
            ->first();

        if (!$phone) {
            return response('Contact Phone Record Not Found', 404);
        }

        return response()->json($phone);
    });

// update a single phone
$app->put('contact/{contact_id}/phone/{phone_id}',
    function (Request $request, $contact_id, $phone_id) use ($rules) {

        $phone = DB::table('phones')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $phone_id)
            ->first();

        if (!$phone) {
            return response('Contact Phone Record Not Found', 404);
        }

        $input = $request->only(['phone']);

        $validator = Validator::make($input, $rules['phone']);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        DB::table('phones')
            ->where('id', (int) $phone->id)
            ->update([
                'phone' => $input['phone']
            ]);

        return response('Updated', 200);
    });

// delete a single phone
$app->delete('contact/{contact_id}/phone/{phone_id}',
    function ($contact_id, $phone_id) {

        $phone = DB::table('phones')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $phone_id)
            ->first();

        if (!$phone) {
            return response('Not Found', 404);
        }

        DB::table('phones')
            ->where('id', (int) $phone->id)
            ->delete();

        return response('Deleted', 200);
    });

/*
 * ----------------------------------------------------------------------------
 * Contact Email Routes
 * ----------------------------------------------------------------------------
 */

// get the emails from a specific contact
$app->get('contact/{contact_id}/email', function ($contact_id) {

    $contact = DB::table('contacts')
        ->where('id', (int) $contact_id)
        ->first();

    if (!$contact) {
        return response('Not Found', 404);
    }

    $emails = DB::table('emails')
        ->where('contact_id', $contact->id)
        ->get();

    return response()->json($emails);
});

// create a new email for a specific contact
$app->post('contact/{contact_id}/email',
    function (Request $request, $contact_id) use ($rules) {

        $contact = DB::table('contacts')
            ->where('id', (int) $contact_id)
            ->first();

        if (!$contact) {
            return response('Not Found', 404);
        }

        $input = $request->only(['email']);

        $validator = Validator::make($input, $rules['email']);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        DB::table('emails')
            ->insert([
                'contact_id' => (int) $contact->id,
                'email'      => $input['email']
            ]);

        return response('Created', 201);

    });

// get a specific email from a specific contact
$app->get('contact/{contact_id}/email/{email_id}',
    function ($contact_id, $email_id) {

        $email = DB::table('emails')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $email_id)
            ->first();

        if (!$email) {
            return response('Not Found', 404);
        }

        return response()->json($email);
    });

// update a specific email for a specific contact
$app->put('contact/{contact_id}/email/{email_id}',
    function (Request $request, $contact_id, $email_id) use ($rules) {

        $email = DB::table('emails')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $email_id)
            ->first();

        if (!$email) {
            return response('Not Found', 404);
        }

        $input = $request->only(['email']);

        $validator = Validator::make($input, $rules['email']);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        DB::table('emails')
            ->where('id', (int) $email->id)
            ->update([
                'email' => $input['email']
            ]);

        return response('Updated', 200);
    });

$app->delete('contact/{contact_id}/email/{email_id}',
    function ($contact_id, $email_id) {

        $email = DB::table('emails')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $email_id)
            ->first();

        if (!$email) {
            return response('Not Found', 404);
        }

        DB::table('emails')
            ->where('id', (int) $email->id)
            ->delete();

        return response('Deleted', 200);
    });
