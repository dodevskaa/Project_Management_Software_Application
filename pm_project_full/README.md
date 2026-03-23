# Project Management Software Specification

## Description
This project management software will serve as a simplified system for managing tasks and projects. The system provides a hierarchical structure where users are categorized by level of experience: **Senior**, **Mid** and **Junior**, with administrators having full control of the system. A subset of senior users can be designated as **Team Leads**, who are responsible for managing teams and projects. 

This project can be divided into 2 parts, the application itself visible to every user after logging in and an administrator version that will be available only to the user designated as an administrator. Projects represent the largest resource managed by the application. The project is assigned to Senior Team Leads. Team Leads then need to assign developers to this project, i.e. create a team. Within the project, Team Leads can create tasks that can then be assigned to team members to complete them. 

Tasks represent the main work item in the application. A Task (or more commonly known as a Ticket) is created to describe the required action to be taken. These actions can be anything from fixing a minor bug to implementing a major project feature. A task has multiple statuses, which are explained later in this document. When a task is initially created by a team lead, it is not assigned to any project member. After being assigned to members, the task's status can be changed by the members. Tasks can also receive comments, with full CRUD capabilities for the comment owner.

## User Roles and Permissions

**Admin:**

Admins have the highest level of control over the system and manage users, projects, tasks, and comments. Admins can:
- Create and manage user accounts.
- Assign user experience levels (Senior, Mid, Junior).
- Designate Seniors as Team Leads.
- Create, update, assign, and delete projects with information such as:
  - Title
  - Description
  - Requirements
  - Estimated Time of Completion
  - Assignment to a Team Lead
  - Deadline

- View and manage all users, projects, tasks, and comments.
- Full CRUD (Create, Read, Update, Delete) access over:
  - User accounts
  - Projects
  - Tasks
  - Comments

_The Admin has full access to every part of the regular application a unique access to the admin panel. Within the regular application the admin has Senior Team Lead clearance to every existing project. Administrators use special data for the registration form, added directly to the database._

**All Users:**

The following features are required for any user:
- User registration
  - Full Name
  - Email
  - Password (The password in the database is hashed.)
  - Repeat Password (Password in Database has to be hashed)
  - Level
- User Log in 
  - Email or username
  - Password
- Change password option
  - Enter old password
  - Enter new password

_The Non-Admin users can be created by the Admin through the Admin Panel or can register themselves through a separate registration form. These users will need to be approved by an admin in order for them to be able to log into the app. Users created by the Admin in the Admin Panel are automatically approved and have random password string generated._

**Senior:**

Seniors are experienced users and can be split into:

- Team Lead: Seniors that are able to lead projects and manage teams.
- Senior: Can perform tasks on assigned projects but does not manage teams (does not lead projects).

**Team Lead:**
- Can be assigned projects by Admins.
- Create their own team using users from the Senior, Mid, and Junior pools.
  - Users can be added and/or deleted.
- Can create, assign, and manage tasks within their assigned project with information:
  - Title
  - Description
  - Can assign tasks to team members.
- Can change the status of a task to To Do, In Progress, QA, or Done.
- Can mark the project as Done once all tasks are complete.
- Leave comments on any task (including unassigned tasks)

_A Team Lead is defined as a Senior who has been assigned the role of Team Lead by the Admin. An Admin can then assign created projects exclusively to Seniors that have been marked as Team Leads prior. Note that a Team Lead’s unique permissions and privileges are scoped (limited) only to the projects that have been assigned to them - meaning - a Team Lead can be a member of a team of a different project as a regular Senior and NOT have the same privileges (creation of tasks) because here he is not the owner i.e. the project was not assigned to him, but has been added by another Team Lead as a regular senior._

**Senior:**
- Cannot manage teams (add/remove members to project).
- Can create and manage tasks within assigned projects but only once they have been added to a team/project by a Team Lead.
- Can change the status of any task.
- Leave comments on any task (including unassigned tasks)

**Mid:**

Mid-level users have the following permissions:
- Cannot create new tasks, only interact with existing ones.
- Can only assign tasks to themselves or Junior users.
- Can change the status of a task only if it is already assigned to them or any task assigned to a Junior.
- Can leave comments on tasks they are involved in and tasks that are assigned to Junior.

**Junior:**

Junior users have the most limited permissions:
- Cannot create new tasks, only interact with tasks already assigned to them by Team Lead/Senior/Mid.
- Cannot assign tasks to self or to others.
- Can change the status of a task only if it is already assigned to them.
- Can leave comments only to tasks assigned to themselves.

## Project and Task Management

**Projects:**
- Created by the Admin with details such as title, description, requirements, estimated time of completion, and deadline.
- Projects are assigned to Team Leads by Admins.
- Only Team Leads can create a team and assign tasks to team members within the project.
- Every Member of the team of a project has access to the project information (defined by the admin during Project creation)

**Tasks:**
- Tasks are created by Team Leads or Admin.
- Tasks have the following statuses:
  - To Do (default status when created)
  - In Progress
  - QA
  - Done
- Team Leads can assign tasks to any team member (Themselves, Other Seniors, Mid, Junior).
- Junior users can only update task statuses assigned to them.

**Comments:**
- Created tasks have a comment section
- Comments must have a timestamp of the time of creation
- Seniors (Team Leads and Non-Team Leads) can leave comments on any task, regardless if the tasks has/has not received an assignee.
- Mid can leave comments on tasks already assigned to them and to tasks assigned to Juniors
- Junior can leave comments only on tasks assigned to them
- Regardless of the User level, every comment owner can Edit and Delete their own comments
- Within the admin panel, an Admin has the clearance to full CRUD operations to any comment of any user

**Teams:**
- Teams represent the group of users that are assigned to a Project.

_**Bonus:**_

User interface feedback throughout the application to improve the user experience. This includes:
- Displaying success and error messages for CRUD operations and form submissions.
- Providing real-time feedback on user interactions to ensure smooth and responsive navigation.
- Handling extreme cases, such as invalid input or server errors, with clear and informative messages.

## Technology Stack

- HTML/CSS/Bootstrap.
- PHP (OOP preferred) for server-side logic, handling CRUD operations for users, projects, tasks, and comments.
- JavaScript (JQuery) for interactive UI elements.
- SQL for storing user data, projects, tasks, comments, and statuses.

**This project management software provides a clear hierarchy of permissions, with responsibilities distributed among Admins, Senior Team Leads, Seniors, Mid-level, and Junior users.**

# Project Management - Extended PHP Implementation

This is a more complete PHP + MySQL implementation of the Project Management specification.

## What is included
- SQL schema: `sql/schema.sql`
- Sample data file: `sql/sample_data.sql`
- Basic authentication (register/login) and session handling.
- Admin panel to approve users and set roles.
- Projects, tasks, members, comments schema and controllers.
- Frontend pages in `public/` (login, register, dashboard, project view, admin panel).
- AJAX endpoints for assigning tasks and changing status.
- API endpoints for creating projects and tasks, adding members, and comment CRUD.
- Helper script to create an admin: `scripts/create_admin.php` (run via CLI).

## Setup (local)
1. Create a MySQL database named `pm_app` and import `sql/schema.sql`:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. Update database credentials in `src/config.php`.
3. Place the `public/` folder as the document root. For quick testing run built-in server:
   ```bash
   php -S localhost:8000 -t public
   ```
4. Create an admin user:
   - Via script (from project root):
     ```bash
     php scripts/create_admin.php "Admin" "admin@example.com" "password123"
     ```
   - Or register via UI and then update the DB to set `approved=1` and `level='Admin'` for that user.

## API endpoints (examples)
- `POST /src/api/create_project.php` — Create a project (admin only). JSON body: `{title,description,requirements,estimated_time,team_lead_id,deadline}`
- `POST /src/api/create_task.php` — Create a task (TeamLead for project). JSON body: `{project_id,title,description}`
- `POST /src/api/add_member.php` — Add member to project (TeamLead). JSON body: `{project_id,user_id,role}`
- `POST /src/api/assign_task.php` — Assign task (TeamLead or according to role rules).
- `POST /src/api/change_status.php` — Change task status (checks permissions).
- `POST /src/api/comment_create.php` — Add comment `{task_id,content}`
- `POST /src/api/comment_edit.php` — Edit comment `{comment_id,content}`
- `POST /src/api/comment_delete.php` — Delete comment `{comment_id}`

