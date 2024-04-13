<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "My App" ?></title>
    <script src="/js/home/index.js"></script>
</head>

<body>
    <h1><?= $title ?? "My App" ?> : Home page</h1>
    <p>Welcome to my app</p>
    <ul>
        <?php foreach ($roles as $role) : ?>
            <li><?= $role->getNom() ?></li>
        <?php endforeach; ?>
    </ul>
    <div>
        <input type="text" id="ext" placeholder="Enter file extension">
        <button id="btn">Download</button>
    </div>
</body>

</html>