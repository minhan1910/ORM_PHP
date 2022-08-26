<?php

$root = dirname(__FILE__);
$root = str_replace('\\', '/', $root);
define('ROOT', $root);
require_once ROOT . '/Core/autoload.php';
require_once ROOT . '/Core/helpers.php';

use Core\Database\CommandBuilder;
use Core\Helpers;

use Models\{
    User,
    Post,
    Profile
};

/**
 * hasOne
 */
// $user = User::first();
// $user->profile()->create([
//     'id' => $user->id,
//     'country' => 'USA',
//     'city' => 'California'
// ]);

// $profile = Profile::first();
// echo $profile->user()->first()->id;



// $post = new Post;
// echo $users = $post->user()->toSql();
// foreach ($users as $user) {
    // echo $user->name;
// }
// $user = User::find(1);
// $posts = $user->posts()->get();
// $post = $user->posts()->where('title', '=', 'Danial second post')->update([
//     'title' => 'Danial simple post'
// ]);
// foreach ($posts as $post) {
//     echo $post->title;
//     echo '<br>';
// }

// $command = $builder
//     ->table('users')
//     ->orWhere('first_name', '=', 'minhan')
//     ->where('last_name', '=', 'an')
//     ->whereIn('first_name', ['123', '123', '123'])
//     ->orWhereIn('age', [10, 20, 30])
//     ->orWhere(function (CommandBuilder $builder) {
//         $builder
//             ->where('age', '>=', 10)
//             ->orWhere('age', '<=', 100)
//             ->where('fullname', 'like', '%an%')
//             ->whereIn('first_name', ['123', '123', '123'])
//             ->where(function (CommandBuilder $builder) {
//                 $builder->orWhere('first_name', '=', 'An');
//             });
//     })
//     ->where(function (CommandBuilder $builder) {
//         $builder
//             ->where('first_name', '=', 'An');
//     })
//     ->getCommandString();

// $command = $builder
//     ->table('users')
//     ->sum('age');
// echo $command;

// $arr = [
//     ['name' => 'An', 'age' => 18],
//     ['name' => 'Huy', 'age' => 18],
// ];

// $arrAssoc = ['name' => 'An', 'age' => 18];
// var_dump($arrAssoc);
// echo $builder->table('users')->insert($arr);
// echo $builder->table('users')->insert($arrAssoc);

// echo $builder
//     ->table('users')
//     ->where('id', '=', 7)
//     ->delete();

// echo $builder
//     ->table('users')
//     ->where('name', '=', 'An')
//     ->update([
//         'name' => 'MinAn'
//     ]);


// $user = new User;
// Helpers::formatArray(
//     $user::select('name')
//         ->where('name', '=', 'MinAn')
//         ->orWhere('name', '=', 'Danial')
//         ->all()
// );

// echo User::select('name')
//     ->where('name', '=', 'Danial')
//     ->avg('age');
// ->toSql();

// $user = User::create([
//     'name' => 'Hun2',
//     'Age' => 19
// ]);
// Helpers::formatObject($user);

// User::where('name', '=', 'Danial')->orWhere('id', '=', 6)->update([
//     'age' => 12
// ]);

// $user = User::find(8);
// Helpers::formatObject($user);
// echo $user->update([
//     'name' => 'Di', 'age' => 20
// ]);

// $user = User::find(4);
// $user->name = 'An123';
// $user->age = 13;
// $user->email = 'an123@gmail.com';
// $user->save();