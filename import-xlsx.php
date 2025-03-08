<?php
$import_type = isset($_GET['type']) ? $_GET['type'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>5Tel - Reports Import</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .slide-up {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .btn {
            @apply px-6 py-3 rounded-lg font-medium text-white transition-all duration-300 hover:shadow-md hover:-translate-y-1 flex items-center justify-center gap-2;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: #3b82f6; /* Matches blue-500 */
            animation: spin 1s ease infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen text-gray-900 antialiased flex flex-col">
    <!-- Header (Fixed at Top) -->
    <header class="w-full bg-white shadow-sm fixed top-0 left-0 z-10">
        <div class="container mx-auto px-4 py-4 max-w-[90vw] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-semibold text-gray-900">5Tel</h1>
                <span class="text-gray-500">|</span>
                <span class="text-sm text-gray-500">Reports Import - <?= ucfirst($import_type) ?> Table</span>
            </div>
        </div>
    </header>

    <!-- Main Content (Centered) -->
    <main class="flex-grow flex items-center justify-center py-16">
        <div class="w-full max-w-md mx-auto relative">
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['error'])): ?>
                <div id="message" class="bg-red-500/90 text-white p-3 rounded-xl mb-6 shadow-md slide-up flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Data import failed, please try again</span>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['success'])): ?>
                <div id="message" class="bg-green-500/90 text-white p-3 rounded-xl mb-6 shadow-md slide-up flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <span>Data imported successfully</span>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 fade-in relative">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 text-center">Import into <?= ucfirst($import_type) ?> Table</h2>

                <!-- Loading Spinner -->
                <div id="loading" class="hidden absolute inset-0 bg-gray-100/75 rounded-xl flex items-center justify-center z-20">
                    <div class="spinner"></div>
                    <h4 class="text-gray-700 font-medium">Importing...</h4>
                </div>

                <!-- Form -->
                <form id="csvForm" action="<?= "action-xlsx-$import_type.php" ?>" method="post" enctype="multipart/form-data">
                    <div class="mb-6">
                        <label for="xlsxFile" class="block text-gray-600 font-medium mb-2">Choose XLSX File</label>
                        <input type="file" name="xlsxFile" id="xlsxFile" accept=".xlsx" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 text-gray-700" required>
                    </div>
                    <button type="submit" class="btn bg-blue-600 hover:bg-blue-700 w-full rounded-xl px-8 py-4 shadow-sm hover:shadow-md transform hover:-translate-y-1 transition-all duration-300">
                        <i class="fas fa-upload"></i>
                        <span>Upload and Process</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer (Fixed at Bottom) -->
    <footer class="w-full py-4 text-center text-sm text-gray-500 bg-gray-100 shadow-inner fixed bottom-0 left-0">
        © <?php echo date('Y'); ?> Powered by <a href="https://vortexweb.cloud" class="hover:underline" target="_blank">VortexWeb</a>.
    </footer>

    <script>
        // Show loading animation on form submit
        document.getElementById('csvForm').addEventListener('submit', function() {
            document.getElementById('loading').classList.remove('hidden');
        });

        // Automatically hide success or error message after 3 seconds
        const messageDiv = document.getElementById('message');
        if (messageDiv) {
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 3000);
        }
    </script>
</body>

</html>