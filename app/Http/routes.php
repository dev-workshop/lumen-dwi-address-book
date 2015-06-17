<?php

use Carbon\Carbon;
use Illuminate\Http\Request;

$rules = [
    'contact' => [
        'first_name'     => 'max:255',
        'last_name'      => 'max:255',
        'birthday_month' => 'integer|min:1|max:12',
        'birthday_year'  => 'integer|min:1900|max:' . date('Y'),
        'birthday_day'   => 'integer|min:1|max:31'
    ],
    'address' => [
        'address_1' => 'max:255',
        'address_2' => 'max:255',
        'city'      => 'max:255',
        'state'     => 'max:255',
        'zip'       => 'max:255',
        'country'   => 'max:255'
    ],
    'phone' => [
        'phone' => 'required|max:100'
    ],
    'email' => [
        'email' => 'required|max:255|email'
    ]
];

$app->get('/', function() use ($app) {
    return $app->welcome();
});

// get all contacts
$app->get('contacts', function() {
    $contacts = DB::table('contacts')->get();

    return response()->json($contacts);
});

// create a new contact
$app->post('contact', function (Request $request) use ($rules) {

    $record = $request->only([
        'first_name', 'last_name', 'birthday_month', 'birthday_day', 'birthday_year'
    ]);

    $validator = Validator::make($record, $rules['contact']);

    if ($validator->fails()) {
        return response($validator->errors(), 400);
    }

    $record['created_at'] = Carbon::now();
    $record['updated_at'] = Carbon::now();

    DB::table('contacts')->insert($record);

    return response('Created', 201);
});

// get a specific contact
$app->get('contact/{id}', function($id) {
    $contact = DB::table('contacts')->where('id', $id)->first();
    return response()->json($contact);
});

// update a specific contact
$app->put('contact/{id}', function (Request $request, $id) use ($rules) {

    $record = $request->only([
        'first_name', 'last_name', 'birthday_month', 'birthday_day', 'birthday_year'
    ]);

    $validator = Validator::make($record, $rules['contact']);

    if ($validator->fails()) {
        return response($validator->errors(), 400);
    }

    $record['updated_at'] = Carbon::now();

    DB::table('contacts')->where('id', $id)->update($record);

    return response('Updated', 200);
});

// delete a contact
$app->delete('contact/{id}/', function ($id) {
    DB::table('contacts')->where('id', $id)->delete();
    return response('Deleted', 200);
});

// get the addresses from a specific contact
$app->get('contact/{id}/address', function ($id) {
    $addresses = DB::table('addresses')->where('contact_id', $id)->get();
    return response()->json($addresses);
});


// add an address to the contact
$app->post('contact/{id}/address', function (Request $request, $id) use ($rules) {

    // make sure that we have a contact
    $contact = DB::table('addresses')->where('contact_id', $id)->count();

    if (!$contact) {
        return response('Contact Record Not Found', 404);
    }

    $address = $request->only([
        'address_1', 'address_2', 'city', 'state', 'zip', 'country',
    ]);

    $validator = Validator::make($address, $rules['address']);

    if ($validator->fails()) {
        return response($validator->errors(), 400);
    }

    $address['contact_id'] = $contact['id'];

    DB::table('addresses')->insert($address);

    return response('Created Address', 201);
});

// get a single contact address
$app->get('contact/{contact_id}/address/{address_id}',
    function ($contact_id, $address_id) {

        // get the contact address
        $address = DB::table('addresses')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $address_id)
            ->get();

        if (!$address) {
            return response('Contact Address Record Not Found', 404);
        }

        return response()->json($address);
    });

// update a single contact address
$app->put('contact/{contact_id}/address/{address_id}',
    function (Request $request, $contact_id, $address_id) use ($rules) {

        // get the contact address
        $address = DB::table('addresses')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $address_id)
            ->get();

        if (!$address) {
            return response('Contact Address Record Not Found', 404);
        }

        $input = $request->only([
            'address_1', 'address_2', 'city', 'state', 'zip', 'country',
        ]);

        $validator = Validator::make($input, $rules['address']);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        $address['address_1'] = $input['address_1'];
        $address['address_2'] = $input['address_2'];
        $address['city']      = $input['city'];
        $address['state']     = $input['state'];
        $address['zip']       = $input['zip'];
        $address['country']   = $input['country'];

        DB::table('addresses')->where('id', $address_id)->update($address);

        return response('Updated Address', 200);
    });

// delete a single address
$app->delete('contact/{contact_id}/address/{address}',
    function ($contact_id, $address_id) {

        DB::table('addresses')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $address_id)
            ->delete();

        return response('Deleted Address', 200);
    });

// get the phones from a specific contact
$app->get('contact/{contact_id}/phone', function ($contact_id) {

    $phones = DB::table('phones')
        ->where('contact_id', (int) $contact_id)
        ->get();

    return response()->json($phones);
});

// create a phone for a specific contact
$app->post('contact/{contact_id}/phone',
    function (Request $request, $contact_id) use ($rules) {

        $input = $request->only(['phone']);

        $validator = Validator::make($input, $rules['phone']);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        DB::table('phones')
            ->insert([
                'contact_id' => (int) $contact_id,
                'phone' => $input['phone']
            ]);

        return response('Contact Phone Created', 201);
    });

// get a single phone
$app->get('contact/{contact_id}/phone/{phone_id}',
    function ($contact_id, $phone_id) {

        $phone = DB::table('phones')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $phone_id)
            ->get();

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
            ->get();

        if (!$phone) {
            return response('Contact Phone Record Not Found', 404);
        }

        $input = $request->only(['phone']);

        $validator = Validator::make($input, $rules['phone']);

        if ($validator->fails()) {
            return response($validator->errors(), 400);
        }

        DB::table('phones')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $phone_id)
            ->update([
                'phone' => $input['phone']
            ]);

        return response('Updated', 200);
    });

// delete a single phone
$app->delete('contact/{contact_id}/phone/{phone_id}',
    function ($contact_id, $phone_id) {

        DB::table('phones')
            ->where('contact_id', (int) $contact_id)
            ->where('id', (int) $phone_id)
            ->delete();

        return response('Deleted', 200);
    });

// get the emails from a specific contact
$app->get('contact/{contact_id}/email', function ($contact_id) {
    $emails = DB::table('emails')->where('contact_id', $contact_id)->get();
    return response()->json($emails);
});