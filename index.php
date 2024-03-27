<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<header>
    <h1>Поиск торгов</h1>
</header>

<section>
    <form method="post" action="process.php">
        <label for="tradeNumber">Номер торгов:</label>
        <input type="text" id="tradeNumber" name="tradeNumber" placeholder="Введите номер торгов (например: 31710-ОТПП)"
               required><br><br>
        <label for="lotNumber">Номер лота:</label>
        <input type="number" id="lotNumber" name="lotNumber" placeholder="Введите номер лота (1-9999)" required><br><br>
        <button type="submit">Найти</button>
    </form>
</section>

<main>
    <?php

    use App\DatabaseHandler;

    require_once 'vendor/autoload.php';

    $databaseHandler = new DatabaseHandler();
    $lots = $databaseHandler->selectLots();

    ?>

    <table>
        <tr>
            <th>URL адрес</th>
            <th>Cведения об имуществе</th>
            <th>Начальная цена лота</th>
            <th>email контактного лица</th>
            <th>Телефон контактного лица</th>
            <th>ИНН должника</th>
            <th>Номер дела о банкротстве</th>
            <th>Дата торгов</th>
        </tr>

        <?php
        while ($row = $lots->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['href'] . "</td>";
            echo "<td class='debtor-info'>" . $row['debtor_property_information'] . "</td>";
            echo "<td>" . $row['start_price'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td class='phone'>" . $row['phone'] . "</td>";
            echo "<td>" . $row['debtor_ITN'] . "</td>";
            echo "<td>" . $row['bankruptcy_case_number'] . "</td>";
            echo "<td>" . $row['date_auction'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</main>

<footer>
    <!-- Дополнительная информация или ссылки -->
</footer>
</body>

</html>