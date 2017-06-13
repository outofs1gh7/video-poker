<?php

/**
 * Created by PhpStorm.
 * User: Ruben
 * Date: 13/06/2017
 * Time: 19:42
 */
class UserController
{
    public function index()
    {
        $users = User::all();
        View::make('home.index', ['users' => $users]);

    }

    /**
     * @return mixed
     */
    public function create()
    {
        View::make('book.create');
    }

    /**
     * @return mixed
     */
    public function store()
    {
        // create new resource (activerecord/model) instance
        // your form name fields must match the ones of the table fields
        $book = new Book(Post::getAll());

        if($book->is_valid()){
            $book->save();
            Redirect::toRoute('book/index');
        } else {
            // return form with data and errors
            Redirect::flashToRoute('book/create', ['book' => $book]);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $book = Book::find($id);

        \Tracy\Debugger::barDump($book);

        if (is_null($book)) {
            // redirect to standard error page
        } else {
            View::make('book.show', ['book' => $book]);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $book = Book::find($id);

        if (is_null($book)) {
            // redirect to standard error page
        } else {
            View::make('book.edit', ['book' => $book]);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $book = Book::find($id);
        $book->update_attributes(Post::getAll());

        if($book->is_valid()){
            $book->save();
            Redirect::toRoute('book/index');
        } else {
            // return form with data and errors
            Redirect::flashToRoute('book/edit', ['book' => $book], $id);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        $book = Book::find($id);
        $book->delete();
        Redirect::toRoute('book/index');
    }
}