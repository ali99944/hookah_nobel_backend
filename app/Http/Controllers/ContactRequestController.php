<?php

namespace App\Http\Controllers;

use App\Enums\ContactRequestStatus;
use App\Http\Resources\ContactRequestResource;
use App\Models\ContactRequest;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    public function index(Request $request)
    {
        $contactRequests = ContactRequest::all();

        return ContactRequestResource::collection($contactRequests);
    }

    public function show(ContactRequest $contactRequest)
    {
        return new ContactRequestResource($contactRequest);
    }

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'name'    => 'required',
            'email'   => 'required|email',
            'message' => 'required',
            'subject' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pendingContactRequest = ContactRequest::where('email', $request->email)
            ->where('status', ContactRequestStatus::Pending->value)
            ->first();

        if ($pendingContactRequest) {
            return response()->json([
                'message' => 'يوجد طلب تواصل قيد المراجعة بنفس البريد الإلكتروني بالفعل',
            ], 409);
        }

        $contactRequest = ContactRequest::create($validator->validated());

        return response()->json([
            'message' => 'تم إرسال طلب التواصل بنجاح',
            'data'    => new ContactRequestResource($contactRequest),
        ], 201);
    }

    public function destroy(ContactRequest $contactRequest)
    {
        $contactRequest->delete();

        return response()->json([
            'message' => 'تم حذف طلب التواصل بنجاح',
        ]);
    }
}
