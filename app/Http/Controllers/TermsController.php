<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function getTerms(Request $request) {
        $userId = auth()->id();

        $query = Term::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('text', 'like', "%{$search}%");
        }

        $terms = $query->get();

        return response()->json($terms);
    }

    public function storeTerm(Request $request) {
        $request->validate([
            'text' => 'required|string'
        ]);

        $term = Term::create([
            'user_id' => auth()->id(),
            'text'    => $request->text,
        ]);

        return response()->json($term, 201);
    }

    public function destroyTerm($id) {
        $term = Term::where('user_id', auth()->id())->findOrFail($id);
        $term->delete();
        return response()->json(['message' => 'Term deleted']);
    }

    // Payment Terms Methods
    public function getPaymentTerms(Request $request) {
		$userId = auth()->id();

		$query = PaymentTerm::query();

		if ($request->filled('search')) {
			$search = $request->search;
			$query->where(function ($q) use ($search) {
				$q->where('description', 'like', "%{$search}%")
				  ->orWhere('value', 'like', "%{$search}%");
			});
		}

		$paymentTerms = $query->get();

		return response()->json($paymentTerms);
	}

    public function storePaymentTerm(Request $request) {
        $request->validate([
            'description' => 'required|string|max:255',
            'value'       => 'nullable|string'
        ]);

        $paymentTerm = PaymentTerm::create([
            'user_id'     => auth()->id(),
            'description' => $request->description,
            'value'       => $request->value,
        ]);

        return response()->json($paymentTerm, 201);
    }

    public function destroyPaymentTerm($id) {
        $paymentTerm = PaymentTerm::where('user_id', auth()->id())->findOrFail($id);
        $paymentTerm->delete();
        return response()->json(['message' => 'Payment term deleted']);
    }
}