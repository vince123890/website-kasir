<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpenSessionRequest;
use App\Http\Requests\CloseSessionRequest;
use App\Http\Requests\ApproveSessionRequest;
use App\Services\StoreSessionService;
use App\Repositories\StoreSessionRepository;
use App\Repositories\CashRegisterRepository;
use App\Repositories\StoreRepository;
use Illuminate\Http\Request;
use Exception;

class SessionController extends Controller
{
    public function __construct(
        protected StoreSessionService $storeSessionService,
        protected StoreSessionRepository $storeSessionRepository,
        protected CashRegisterRepository $cashRegisterRepository,
        protected StoreRepository $storeRepository
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $filters = [
            'store_id' => $request->get('store_id'),
            'cashier_id' => $request->get('cashier_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $sessions = $this->storeSessionRepository->getAllPaginated(15, $search, $filters);
        $stores = $this->storeRepository->getAll();

        return view('sessions.index', compact('sessions', 'stores'));
    }

    public function show($id)
    {
        try {
            $data = $this->storeSessionService->getSessionDetails($id);
            return view('sessions.show', $data);
        } catch (Exception $e) {
            return redirect()->route('sessions.index')
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        $currentSession = $this->storeSessionService->getCurrentSession(
            auth()->id(),
            auth()->user()->store_id
        );

        if ($currentSession) {
            return redirect()->route('sessions.show', $currentSession->id)
                ->with('info', 'You already have an open session.');
        }

        $stores = $this->storeRepository->getAll();
        $registers = $this->cashRegisterRepository->getActiveForStore(auth()->user()->store_id);

        return view('sessions.open', compact('stores', 'registers'));
    }

    public function store(OpenSessionRequest $request)
    {
        try {
            $result = $this->storeSessionService->openSession($request->validated());

            return redirect()->route('pos.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function closeForm($id)
    {
        try {
            $data = $this->storeSessionService->getSessionDetails($id);

            if ($data['session']->status !== 'open') {
                return redirect()->route('sessions.show', $id)
                    ->with('error', 'Only open sessions can be closed');
            }

            return view('sessions.close', $data);
        } catch (Exception $e) {
            return redirect()->route('sessions.index')
                ->with('error', $e->getMessage());
        }
    }

    public function close($id, CloseSessionRequest $request)
    {
        try {
            $result = $this->storeSessionService->closeSession($id, $request->validated());

            return redirect()->route('sessions.show', $id)
                ->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function approve($id, ApproveSessionRequest $request)
    {
        try {
            $result = $this->storeSessionService->approveSession($id, $request->validated());

            return redirect()->route('sessions.show', $id)
                ->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function pendingApprovals(Request $request)
    {
        $sessions = $this->storeSessionRepository->getPendingApproval(15);

        return view('sessions.pending', compact('sessions'));
    }
}
