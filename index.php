<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Import for Bitrix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.3/tailwind.min.css">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg relative">
        <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">Reports SPA Import</h2>

        <!-- Import Buttons -->
        <div class="flex justify-center gap-4">
            <!-- <a href="import-csv.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50" onclick="importCSV()">
                Import CSV
            </a> -->
            <a href="import-xlsx.php?type=user" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-50" onclick="importXLSX()">
                Import Users XLSX
            </a>
            <a href="import-xlsx.php?type=company" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-50" onclick="importXLSX()">
                Import Company XLSX
            </a>
            <a href="import-xlsx.php?type=transaction" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-50" onclick="importXLSX()">
                Import Transaction XLSX
            </a>
        </div>

    </div>
</body>

</html>
