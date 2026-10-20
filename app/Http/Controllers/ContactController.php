<?php
namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\NewsletterSubscriber;
use App\Services\StoreMailer;
use Illuminate\Http\Request;

class ContactController extends Controller
{
	public function index()
	{
		return view('store.contact');
	}

	public function send(Request $request, StoreMailer $mailer)
	{
		$data = $request->validate([
			'name' => 'required|string|max:120',
			'email' => 'required|email|max:190',
			'phone' => 'nullable|string|max:20',
			'subject' => 'required|string|max:190',
			'message' => 'required|string|max:5000',
		]);

		$enquiry = Enquiry::create($data);
		$mailer->enquiry($enquiry);

		return back()->with('success', 'Thank you. Your enquiry has been saved and our team will follow up.');
	}

	public function subscribe(Request $request, StoreMailer $mailer)
	{
		$data = $request->validate(['email' => 'required|email|max:190']);
		$subscriber = NewsletterSubscriber::firstOrCreate(['email' => $data['email']]);

		if ($subscriber->wasRecentlyCreated) {
			$mailer->subscriber($subscriber);
		}

		return back()->with('success', 'You are subscribed to Surat NX updates.');
	}
}
