# Wewillapp Format Response For Laravel 5+

## Instruction

1. Copy this code and paste in `app\Exceptions\Handler.php`

```php
if ($request->wantsJson()) {
    return \Wewillapp\FormatResponse::render($exception);
}
```


Copy &copy; 2019 [Wewillapp](https://www.wewillapp.com) All right Reserved. Created by Natakorn Chanasumon

