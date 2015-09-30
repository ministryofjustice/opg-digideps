#Complete the Deputy Report (Api)

beta version

Return codes
------------
* 404 not found
* 401 auth info missing. e.g. 
    - AuthToken not present or not valid or not matching an existing user
    - User associated to the token doesn't exist any longer
* 403 wrong credentials.e.g
   - missing client secret (for login operation)
   - password is valid but the client secret permission are not included in the user permissions
* 498 wrong credentials at login
* 419 auth token expired
* 500 generic error due to internal exception (e.g. db offline)

