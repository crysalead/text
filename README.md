# Text - String manipulation library

[![Build Status](https://travis-ci.org/crysalead/text.png?branch=master)](https://travis-ci.org/crysalead/text)
[![Code Coverage](https://scrutinizer-ci.com/g/crysalead/text/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/crysalead/text/)

A small library that replaces placeholders into a string template.

## API

### Replacing some placeholders

```php
Text::insert('My name is {:name} and I am {:age} years old.', [
    'name' => 'Bob', 'age' => '65'
]);
```
