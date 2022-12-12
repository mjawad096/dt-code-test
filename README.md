## What makes it amazing code

1. Proper indentation
2. PHPDocs added for each property and method. It made it easy to understand the expected parameter types and return types etc. 
3. Repository pattern used, so our logic exists separate from our interface (Request/response).
4. Base Repository with basic methods that will make it easy to add new repositories extending that one.
5. Base Repository has methods like create, update, etc that wrap the actual logic. In that way, we can easily override our data layer to different data sources.
6. Many (Not all) PSR rules are followed e.g. class, methods and property names, indentation with 4 spaces, type hinting (via PHPDoc), etc.

## What makes it ok code

1. Returns types are mixed for all methods in BookingController, it should be the actual response type that will be returned.
2. _validate method in the BaseRepository as the logic can also be implemented directly in the validate method.
3. Accessors (e.g. getModel) in the BaseRepository
    
## What makes it terrible code

1. Too many methods in a single controller
2. This condition `isset($data['distance']) && $data['distance'] != ""` can be simply written `!empty($data['distance'])`
3. Too many if-else statements we can use the `optional` helper and then the short-ternary operator. See below example
```PHP
//Before
 if (isset($data['distance']) && $data['distance'] != "") {
    $distance = $data['distance'];
} else {
    $distance = "";
}

//After
$data = optional($data);
$distance = $data['distance'] ?: '';
```
4. Extra variable declared that are not being used. e.g. `$affectedRows` and `$affectedRows1` in the controller.
5. `env` directly call in the controller 
6. The curly braces should be on the same line with the if-else statments. 

## How would I have done it

1. I've refactored the BookingController plus some methods of the BookingRepository (Didn't updated whole class as it has too many methods that are time taking and also I don't have an idea about the the requirements of the actual class.)
2. I've updated as per the latest version of the PHP 8.2