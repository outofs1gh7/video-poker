<?php

use ArmoredCore\Controllers\BaseController;
use ArmoredCore\WebObjects\Redirect;
use ArmoredCore\WebObjects\Session;
use ArmoredCore\WebObjects\View;
use ArmoredCore\Interfaces\ResourceControllerInterface;
use ArmoredCore\WebObjects\Post;

class UsersController extends BaseController
{
    /**
     * 
     * @return mixed
     */
    public function index($page = 1) 
    {
        if (isset($_SESSION['user'])) {
            // User is logged in

            // Get authenticated user
            $user = Session::get('user');

            if ($user->isadmin) { // User has admin privileges

                // Get all the users except the authenticated user
                $users = User::find('all', [
                    'conditions' => ['id <> ?', $user->id]
                ]);

                // Total Number of users
                $total_users = count($users);

                // Users we want to show per page
                $users_per_page = 5;
                
                // Total Number of Pages
                $total_pages = ceil($total_users / $users_per_page);

                if ($page < 1) {
                    // Go to the first page
                    $page = 1;
                } else if ($page > $total_pages) {
                    // Go to the last page
                    $page = $total_pages;
                }

                // First index of each page
                $index = ($users_per_page * $page) - ($users_per_page -1);

                return View::make('back-office.users', [
                    'users' => $users,
                    'page'  => $page,
                    'pages' => $total_pages,
                    'index' => $index
                ]);                           
            }
            // User doesn't have admin privileges, so we redirect him to his profile
            return Redirect::toRoute('user/profile');
        }
        // No user is logged in
        return Redirect::toRoute('auth/login');
    }

    /**
     * @return \ArmoredCore\WebObjects\View
     */
    public function register() 
    {
        $_SESSION['fullNameError'] = null;
        $_SESSION['usernameError'] = null;
        $_SESSION['emailError'] = null;
        $_SESSION['passwordError'] = null;
        return View::make('user.form');
    }

    /**
     * @return mixed
     */
    public function store() 
    {
        $data = Post::getAll();
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $user = User::create($data);

        if ($user) {
            return View::make('auth.login');            
        }

        return;
    }

    /**
     * @param $id
     * @return \ArmoredCore\WebObjects\View
     */
    public function profile() 
    {
        $_SESSION['fullNameError'] = null;
        $_SESSION['emailError'] = null;
        $_SESSION['passwordError'] = null;

        if (isset($_SESSION['user'])) {
            $user = User::find(Session::get('user')->id);
 
            return View::make('user.form', ['user' => $user]);
        }

        return Redirect::toRoute('auth/login');    
    }

    /**
     * @param $id
     * @return mixed
     */
    public function update() 
    {
        if (isset($_SESSION['user'])) {
            // Get authenticated user
            $user = Session::get('user');

            // Post data
            $data = Post::getAll();        

            // Name
            if (isset($data['fullName'])) { 
                if (strlen($data['fullName']) > 60) {
                    $_SESSION['fullNameError'] = 'Name content must be less than 60 characters';
                } else {
                    $user->fullname = $data['fullName'];
                }
            }

            // Email
            if (isset($data['email'])) {
                $email_exists = User::find_by_email($data['email']);

                if ($email_exists && $user->email != $email_exists->email) {
                    $_SESSION['emailError'] = 'This email address is already in use.';
                } else {
                    $user->email = $data['email']; 
                }
            }

            // Password    
            if (isset($data['password'])) {
                if (strlen($data['password']) < 8 && strlen($data['password']) != 0) {
                    $_SESSION['passwordError'] = 'Password must be at least 8 characters';
                } else {
                    $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
                }
            }
            
            // $user->save();

            return View::make('user.form', ['user' => $user]);
        }

        return Redirect::toRoute('auth/login');
    }

    /**
     * @param $page
     * @return mixed
     */
    public function getAccountMovements($page = 1) 
    {
        if (isset($_SESSION['user'])) {
            $account = User::find($_SESSION['user']->id);

            // Total Number of movements for the current account
            $total_movements = count($account->movements);

            // Movements we want to show per page
            $movements_per_page = 5;
            
            // Total Number of Pages
            $total_pages = ceil($total_movements / $movements_per_page);

            if ($page < 1) {
                // Go to the first page
                $page = 1;
            } else if ($page > $total_pages) {
                // Go to the last page
                $page = $total_pages;
            }

            // First index of each page
            $index = ($movements_per_page * $page) - ($movements_per_page -1);

            $movements = Movement::find_all_by_userId($account->id, [
                'limit' => $movements_per_page,
                'offset' => $index - 1,
                'order' => 'created_at desc'
            ]);

            return View::make('user.movements', [
                'accountMovements' => $movements,
                'page' => $page,
                'pages' => $total_pages,
                'index' => $index
            ]);
        }

        return Redirect::toRoute('auth/login');
    }
}