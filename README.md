Installation Guide :)

1. **Clone the repository**

   git clone https://github.com/logic-zero/BACKEND_TASK.git
   
   cd BACKEND_TASK


2. **Install PHP dependencies (Composer)**

   composer install

3. **Install JS dependencies (Node/NPM)**

   npm install

4. **Create the `.env` file**

   copy .env.example then rename to .env

5. **Generate application key**

   php artisan key:generate

6. **Set up database**

   * Open `.env`
   * Update DB settings

     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=your_database_name
     DB_USERNAME=root
     DB_PASSWORD=

7. **Run migrations with seeder**

   php artisan migrate --seed

8. **Build frontend assets**

   npm run dev

10. **Serve the app**

    php artisan serve

    App will run at 👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)

After that register and then login,(theres a sample ui for the backend after login)



other info

## 🛠 ORM vs Query Builder

* **Eloquent ORM**
  Used in `ProjectController@index` to fetch projects with their owner:

  $projectsQuery = Project::with('user');

* **Query Builder**
  Used in `ProjectController@index` to count total and completed tasks per project:

  DB::table('tasks')
      ->select('project_id', DB::raw('count(*) as total'), DB::raw("SUM(status = 'completed') as completed"))
      ->groupBy('project_id')
      ->get();


## Routes(web.php)
    //projects
    Route::resource('projects', ProjectController::class);

    //task
    Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::post('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::get('users/{user}/tasks', [TaskController::class, 'tasksByUser'])->name('users.tasks');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    //task comments
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');

