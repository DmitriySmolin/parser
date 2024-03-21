<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>

<body>
	<form method="post" action="process.php">
		<label for="tradeNumber">Номер торгов:</label>
		<input type="text" id="tradeNumber" name="tradeNumber" placeholder="Введите номер торгов (например: 31710-ОТПП)"
			required><br><br>
		<label for="lotNumber">Номер лота:</label>
		<input type="number" id="lotNumber" name="lotNumber" placeholder="Введите номер лота (1-9999)" required><br><br>
		<input type="submit" value="Найти">
	</form>
</body>

  
</html>

