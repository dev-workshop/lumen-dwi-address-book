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

// get the phones from a specific contact
$app->get('contact/{contact_id}/phone', function ($contact_id) {
    $phones = DB::table('phones')->where('contact_id', $contact_id)->get();
    return response()->json($phones);
});

// get the emails from a specific contact
$app->get('contact/{contact_id}/email', function ($contact_id) {
    $emails = DB::table('emails')->where('contact_id', $contact_id)->get();
    return response()->json($emails);
});