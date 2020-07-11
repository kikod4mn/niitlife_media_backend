# Photographer Website API backend.

### Resources

>*If not otherwise stated, a specified path is open to the public and requires no auth.*

___

#### Post

##### Urls

- path=__"/posts/{page}"__, method=__"GET"__ - Collection of posts, paginated<br>
*Page is an optional parameter*

- path=__"/posts"__, method=__"POST"__ - Save a new post. 
Requires administrative rights over the blog. <br> 
__Must include "title" and "body" fields in the request content.__

- path=__"/posts/{id}"__, method=__"GET"__ - Get a single post by its ID<br>
*Id is a mandatory param*

- path=__"/posts/{slug}"__, method=__"GET"__ - Get a single post by its slug<br>
*Slug is a mandatory param*

- path=__"/posts/{id}/update"__, method=__"PUT"__ - Update post body or title.<br>
Requires administrative rights over the blog. <br> 
*Id is a mandatory param*<br>
__Must include EITHER "title" or "body" fields in the request content.__

- path=__"/posts/{id}/image/update"__, method=__"PUT"__ - Update a post's leading image.<br>
Requires administrative rights over the blog. <br> 
*Id is a mandatory param*<br>
__Must include "imgId" field that is a valid uuid in the request content.__

- path=__"/posts/{id}/category/update"__, method=__"PUT"__ - Update a post's category.<br>
Requires administrative rights over the blog. <br> 
*Id is a mandatory param*<br>
__Must include "catId" field that is a valid uuid in the request content.__

- path=__"/posts/{id}/tags/update"__, method=__"PUT"__ - Update a post's tags.<br>
Requires administrative rights over the blog. <br> 
*Id is a mandatory param*<br>
__Must include "tagIds" field as an array of valid tag uuids in the request content.__

- path=__"/posts/{id}/trash"__, method=__"DELETE"__ - Trash a post. Will hide from public view.<br>
Administrator can still view and edit, also re-publish the post.<br>
Requires administrative rights over the blog. <br> 
*Id is a mandatory param*<br>

- path=__"/posts/{id}/destroy"__, method=__"DELETE"__ - Delete a post from the database.<br>
Requires administrative rights over the blog. <br> 
*Id is a mandatory param*<br>

___

#### Post Comments

##### Urls

- path=__"/posts/{postId}/comments/{page}"__, method=__"GET"__ - Collection of comments for a post, paginated<br>
*Page is an optional parameter, PostId is mandatory*

- path=__"/posts/{postId}/comments"__, method=__"POST"__ - Save a new post. 
Requires user to be logged in and not be muted. <br> 
__Must include "body" field in the request content.__

- path=__"/comments/{id}"__, method=__"GET"__ - Get a single post by its ID<br>
*Id is a mandatory param*

- path=__"/comments/{id}/update"__, method=__"PUT"__ - Update comment body.<br>
Requires user to be logged in, not be muted and be author of said comment. <br> 
*Id is a mandatory param*<br>
__Must include "body" field in the request content.__

- path=__"/comments/{id}/trash"__, method=__"DELETE"__ - Trash a comment. Will hide from public view.<br>
Administrator and author can still view and edit, also re-publish the comment.<br>
Requires administrative rights over the blog. <br> 
*Id is a mandatory param*<br>

- path=__"/comments/{id}/destroy"__, method=__"DELETE"__ - Delete a post from the database.<br>
Requires administrative rights or user to be the author of the comment. <br> 
*Id is a mandatory param*<br>

___

### User

> No public user info viewing is possible. Only id, username, full name and profile avatar are visible on resources.
>
#### Urls

- path=__"/register"__, method=__"POST"__ - Register a new user.<br>
*Will return a response with the user info and JWT web token if successful*<br>
__Must include "username", "fullname", "email", "plainPassword" and "retypedPlainPassword" fields in the request content.__

- path=__"/login"__, method=__"POST"__ - log the user in.<br>
*Will return a response with the user info and JWT web token if successful*<br>
__Must include "username" and "password" fields in the request content.__

- path=__"/account"__, method=__"GET"__, - View own profile.<br>
Requires user to be logged in<br>

- path=__"/account/update"__, method=__"PUT"__, - Update own profile.<br>
Requires user to be logged in<br>
__Must include EITHER "fullname", "email" or "plainPassword" and "retypedPlainPassword" fields in the request content.__

-path=__"/profile"__, method=__"GET"__ - view own profile.<br>
Requires user to be logged in<br>

- path=__"/profile/update"__, method=__"PUT"__, - Update own profile.<br>
Requires user to be logged in<br>
__Must include "avatar" field in the request content. Field can be null for default avatar to be used.__