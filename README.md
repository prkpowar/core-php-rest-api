
## About Core PHP REST API


- User Registration, Login 
- Create task with notes and Get All the tasks

## Available routes

- GET /seed/data
- POST - /login
- POST - /register
- GET - /task/all
- POST /task/create

## POSTMAN Collection

- Please check/add postman collection added in the project to check how to call API.

## Installation

- run "cp .env.example .env" & Add env variables as required
- run "composer install"
- run route "/seed/data" to create database, tables & populate sample data
- run "composer dump-autoload" if any class not found error occurs
