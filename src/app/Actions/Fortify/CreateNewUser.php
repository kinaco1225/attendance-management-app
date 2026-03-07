<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Http\Requests\RegisterRequest;

class CreateNewUser implements CreatesNewUsers
{
  public function create(array $input): User
  {
    // ✅ 登録バリデーション
    Validator::make(
      $input,
      (new RegisterRequest())->rules(),
      (new RegisterRequest())->messages()
    )->validate();

    return DB::transaction(function () {

      $last = User::lockForUpdate()
        ->where('employee_number', 'like', 'U%')
        ->max('employee_number');

      $next = $last ? ((int) substr($last, 1) + 1) : 1;

      return User::create([
        'name' => request('name'),
        'email' => request('email'),
        'password' => Hash::make(request('password')),
        'role' => 'user',
        'employee_number' => 'U' . str_pad($next, 4, '0', STR_PAD_LEFT),
      ]);
    });
  }
}
