<?php

namespace App\Http\Controllers\Admin;

use App\Department;
use App\Http\Controllers\Controller;
use App\Mail\UserCreated;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * @var Illuminate\Auth\Passwords\PasswordBroker 
     */
    private $passwordBroker;

    /**
     * PasswordResetController constructor.
     * @param Illuminate\Auth\Passwords\PasswordBroker $passwordBroker
     */
    public function __construct(\Illuminate\Auth\Passwords\PasswordBroker $passwordBroker)
    {
        $this->passwordBroker = $passwordBroker;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('departments')->with('markets')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::all();

        return view('admin.users.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => Hash::make(str_random(20)),
        ]);

        $user->departments()->attach(request('departments'));
        $user->markets()->attach(request('markets'));

        $this->sendPasswordReset($user);

        return redirect()->route('admin.users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $departments = Department::all();

        return view('admin.users.edit', compact('departments', 'user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        request()->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        $user->update(request(['name', 'email']));

        $user->departments()->sync(request('departments'));
        $user->markets()->sync(request('markets'));

        return redirect()->route('admin.users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->departments()->detach();
        $user->markets()->detach();

        $user->delete();

        return redirect()->route('admin.users.index');
    }


    public function sendPasswordReset($user)
    {
        $user = User::where("email", $user->email)->first();
        $token = $this->passwordBroker->createToken($user);

        Mail::to($user->email)->send(
            new UserCreated($user, $token)
        );

        // This is old manual way. Using app password broker here instead
        // method from here: https://laracasts.com/discuss/channels/laravel/reset-password-manually-without-email
        // $reset_token = hash_hmac('sha256', Str::random(40), $this->hashKey);
        // DB::table('password_resets')->insert( ['email' => $user->email, 'token' => $reset_token, 'created_at' => Carbon::now()] );
    }
}
