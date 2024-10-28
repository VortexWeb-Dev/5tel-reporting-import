<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Import for Bitrix</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.0.3/tailwind.min.css">
    <style>
        /* Loading spinner styles */
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #3498db;
            animation: spin 1s ease infinite;
            margin-right: 5px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <?php if (isset($_GET['error'])): ?>
        <div id="message" class="bg-red-500 text-white p-3 rounded mb-4 absolute top-2 right-2">
            Data import failed, please try again
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div id="message" class="bg-green-500 text-white p-3 rounded mb-4 absolute top-2 right-2">
            Data imported successfully
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md relative">
        <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">Reporting SPA Import</h2>

        <!-- Loading Spinner -->
        <div id="loading" class="hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
            <div class="spinner"></div>
            <h4>Importing...</h4>
        </div>

        <!-- Form -->
        <form id="csvForm" action="action-xlsx.php" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="xlsxFile" class="block text-gray-600 font-medium mb-2">Choose XLSX File</label>
                <input type="file" name="xlsxFile" id="xlsxFile" accept=".xlsx" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600" required>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">Upload and Process</button>
        </form>
    </div>

    <script>
        // JavaScript to show loading animation on form submit
        document.getElementById('csvForm').addEventListener('submit', function() {
            document.getElementById('loading').classList.remove('hidden');
        });

        // Automatically hide success or error message after a few seconds
        const messageDiv = document.getElementById('message');
        if (messageDiv) {
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000); // Hide after 3 seconds
        }
    </script>
</body>

</html>
