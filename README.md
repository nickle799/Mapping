# Mapping
This is a way of providing a mappable interface to specific parts of your code for the purpose of reports / integrations

# Language
* A parsing is made up of parts.
* A part is defined as a set of code that can be individually parsed.  This can be a direct string, or a series of methods on an object
* These parts can be combined together using the + operator
* If a part starts and ends with double quotes, it is parsed as directly that string
    * eg: "John"+"Smith" will be parsed as two parts, and then combined together to make JohnSmith
* Otherwise, a part must be a defined in the mappable fields on the current object
* You can traverse down related mappable fields with the . operator
    * eg: if you start with a mouse object: computer.room.house.address.city would return the city
* You can also parameters, if they are defined with the mappable field
* eg: computer.room.relatedRoom("below") will get you the room below the mouse's room
* These sub parameters will each be parsed similar to how the original object is parsed, and then the final output will be passed in
    * eg: computer.room.relatedRoom(computer.setting("roomDirection")) will get you the room based off of the computers current roomDirection setting
* As you can see, these parsings can be nested
* If there is more than one parameter, there should be a comma in between (spaces are optional)
    * eg: computer.room.relatedRoom("below", "2") will get you the room two floors below

## Built in Methods
### These methods are always available (Provided they apply to the given current data)

* date("m/d/Y")
    * Parameters
        * Format (Required)
    * This uses the http://php.net/manual/en/function.date.php to format a date object
* map("compareAgainst", "output", ..{2})
    * Parameters
        * Check Against (Required)
        * Parse (Required)
        * Arguments must come in pairs
    * It will compare against the first of each pair
    * if it matches the first part, it will parse the second part use that as the current object
    * If the checkAgainst is the parsed as "*" this is considered the wildcard option, and if no other option matches is used
    * If you put a * anywhere in the string (other than being the entire string) it is parsed as a wildcard, with any characters valid
        * eg: abc*ghi will match abcdefghi, abcghi, and abc123ghi
    * This is a case sensitive match, so you might wish to run a toLowerCase() first
* add("5")
    * Parameters
        * value (Required)
    * This will add a value to the current value
* subtract("5")
    * Parameters
        * value (Required)
    * This will add a value to the current value
* multiply("5")
    * Parameters
        * value (Required)
    * This will add a value to the current value
* divide("5")
    * Parameters
        * value (Required)
    * This will add a value to the current value
* ifThen("true", "false")
    * Parameters
        * trueOption (Optional) defaults to the string of "Yes"
        * falseOption (optional) defaults to the string of "No"
* toLowerCase()
    * Parameters (None)
    * Converts to a lower case string
* toUpperCase()
    * Parameters (None)
    * Converts to a upper case string
* lessThan("6")
    * Parameters
        * value (Required)
    * Compares against the passed in value, and returns if the current one is lessThan or not
* greaterThan("6")
    * Parameters
        * value (Required)
    * Compares against the passed in value, and returns if the current one is greaterThan or not
* substring("5", "6")
    * Parameters
        * start (Required)
        * length (Optional)
    * See http://php.net/manual/en/function.substr.php
    * Values can be negative
* trim(" ")
    * Parameters
        * characterMask (Optional)
    * See http://php.net/manual/en/function.trim.php
* round("2")
    * Parameters
        * precision (Optional) Defaults to 0
* leftFill("5", "0")
     * Parameters
        * length (Required) The length to fill the string to
        * fill (Required) The padding string
    * This adds characters to the left side of the string
* rightFill("5", " ")
     * Parameters
        * length (Required) The length to fill the string to
        * fill (Required) The padding string
    * This adds characters to the right side of the string
* in("a", "b")
    * Parameters
        * comparison (Required) 
        * There must be at least one
    * Returns a boolean that can then be piped into .ifThen

## TODO

* count()
    * Parameters (None)
    * Returns the size of an array if the current object is an array
* itemAt("1")
    * Parameters
        * index (Required)
    * Gets an item at a specified index if the current object is an array
    * Index starts at 0
* subObject()
    * Parameters (None)
    * This gets the object that was current when passing in to the current method
        * This only works if you are inside a method
    * eg: computer.room.computers.itemAt(subObject().length().minus("1"))
        * This will get you the last computer in the array of computers in the current room