<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@taskflow.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create regular users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@taskflow.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@taskflow.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@taskflow.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Create projects
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Complete redesign of the company website',
            'created_by' => $admin->id,
        ]);
        $project1->members()->attach([$user1->id, $user2->id]);

        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Build a new mobile application',
            'created_by' => $admin->id,
        ]);
        $project2->members()->attach([$user2->id, $user3->id]);

        $project3 = Project::create([
            'name' => 'Marketing Campaign',
            'description' => 'Launch new marketing campaign',
            'created_by' => $admin->id,
        ]);
        $project3->members()->attach([$user1->id, $user3->id]);

        // Create tasks
        Task::create([
            'title' => 'Design homepage mockup',
            'description' => 'Create initial design mockup for the homepage',
            'status' => 'IN_PROGRESS',
            'priority' => 'HIGH',
            'due_date' => now()->addDays(5),
            'project_id' => $project1->id,
            'assignee_id' => $user1->id,
        ]);

        Task::create([
            'title' => 'Implement user authentication',
            'description' => 'Build authentication system for the app',
            'status' => 'TODO',
            'priority' => 'CRITICAL',
            'due_date' => now()->addDays(7),
            'project_id' => $project2->id,
            'assignee_id' => $user2->id,
        ]);

        Task::create([
            'title' => 'Create social media posts',
            'description' => 'Design and schedule social media content',
            'status' => 'DONE',
            'priority' => 'MEDIUM',
            'due_date' => now()->subDays(2),
            'project_id' => $project3->id,
            'assignee_id' => $user1->id,
        ]);

        Task::create([
            'title' => 'Write API documentation',
            'description' => 'Document all API endpoints',
            'status' => 'TODO',
            'priority' => 'MEDIUM',
            'due_date' => now()->subDays(1), // Overdue task
            'project_id' => $project2->id,
            'assignee_id' => $user3->id,
        ]);
    }
}
