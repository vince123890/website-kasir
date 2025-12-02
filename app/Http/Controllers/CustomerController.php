<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Services\CustomerService;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Exception;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected CustomerRepository $customerRepository
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $filters = [
            'is_active' => $request->get('is_active'),
        ];

        $customers = $this->customerRepository->getAllPaginated(15, $search, $filters);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request)
    {
        try {
            $result = $this->customerService->createCustomer($request->validated());

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('customers.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $customer = $this->customerRepository->findById($id);

            if (!$customer) {
                return redirect()->route('customers.index')
                    ->with('error', 'Customer not found');
            }

            $stats = $this->customerRepository->getCustomerStats($id);

            return view('customers.show', compact('customer', 'stats'));
        } catch (Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $customer = $this->customerRepository->findById($id);

            if (!$customer) {
                return redirect()->route('customers.index')
                    ->with('error', 'Customer not found');
            }

            return view('customers.edit', compact('customer'));
        } catch (Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', $e->getMessage());
        }
    }

    public function update($id, CustomerRequest $request)
    {
        try {
            $result = $this->customerService->updateCustomer($id, $request->validated());

            return redirect()->route('customers.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->customerService->deleteCustomer($id);

            return redirect()->route('customers.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function searchByPhone(Request $request)
    {
        $phone = $request->get('phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is required',
            ], 400);
        }

        $result = $this->customerService->searchByPhone($phone);

        return response()->json($result);
    }

    public function transactionHistory($id, Request $request)
    {
        try {
            $customer = $this->customerRepository->findById($id);

            if (!$customer) {
                return redirect()->route('customers.index')
                    ->with('error', 'Customer not found');
            }

            $transactions = $this->customerRepository->getTransactionHistory($id, 15);

            return view('customers.history', compact('customer', 'transactions'));
        } catch (Exception $e) {
            return redirect()->route('customers.index')
                ->with('error', $e->getMessage());
        }
    }
}
