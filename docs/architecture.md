# Architecture

This is a conventional Laravel MVC application suitable for a student project. It is not a microservice, DDD or Clean Architecture application.

```text
Browser -> Routes -> Middleware (auth, account.active, role)
        -> Controller -> Form Request / Policy or Gate
        -> Eloquent Model -> MySQL -> Blade Response
```

The employee-code availability interaction uses Blade JavaScript/Fetch, a JSON endpoint, controller validation/query and MySQL. Shared Blade layouts and partials provide the presentation. Eloquent relationships connect User, Employee, Department and Attendance.
