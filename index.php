<?php
// Root entry redirect for shared hosting (e.g. InfinityFree htdocs)
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: public/index.php' . $queryString);
exit;
