<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Account management. Every route here sits behind the "admin" middleware.
 */
class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * List all accounts.
     */
    public function index(): View
    {
        $users = User::query()
            ->withCount('feeds')
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the account creation form.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a new account.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $generated = blank($data['password'] ?? null);

        $result = $this->userService->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? null,
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        $redirect = redirect()->route('users.index')
            ->with('status', sprintf('Account %s created.', $result['user']->email));

        // A generated password is shown exactly once, so the admin can write it
        // down before it disappears from the screen.
        return $generated
            ? $redirect->with('generated_password', [
                'email' => $result['user']->email,
                'password' => $result['password'],
            ])
            : $redirect;
    }

    /**
     * Remove an account and everything it owns.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->is_admin && User::query()->where('is_admin', true)->count() === 1) {
            return back()->withErrors(['user' => 'The last administrator cannot be deleted.']);
        }

        // Feeds and their articles are removed by the database cascade.
        $user->delete();

        return redirect()->route('users.index')
            ->with('status', sprintf('Account %s deleted.', $user->email));
    }
}
