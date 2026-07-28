<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $users = User::latest()->get();
        return view('user-configure.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $request->session()->forget('success');
        return view('user-configure.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|min:6',
            'role'=>'required|in:0,1,2'
        ],[
            'email.unique' => 'This email already registered',
        ]);

        User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'role'=>$request->role
        ]);

        return redirect()->route('user-configuration.index')->with('success', 'User added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        //
        $request->session()->forget('success');
        $user = User::findOrFail(decryptId($id));
    
        return view('user-configure.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($id);
        $user = User::findOrFail(decryptId($id));

        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users,email,'.$user->id,
            'role'=>'required|in:1,2'
        ],[
            'email.unique'  => 'This email already registered',
        ]);

        $data=[
            'name'=>$request->name,
            'email'=>$request->email,
            'role'=>$request->role
        ];

        if($request->filled('password')){
            $data['password']=Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user-configuration.index')
            ->with('success','User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $user = User::findOrFail(decryptId($id));
        
        $user->delete();

        return redirect()->route('user-configuration.index')
            ->with('success','User deleted successfully.');
    }
}
