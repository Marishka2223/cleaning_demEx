<?php

session_start();  // если сессия еще не начата
session_unset();  // удалить все данные сессии
session_destroy(); // уничтожить сессию
header("Location: index.php"); //редирект на страницу авторизации

?>