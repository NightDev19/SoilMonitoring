<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "connection.php";

$data = array();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        main table {
            text-align: center;
            border-collapse: collapse;
            box-shadow: 5px 5px 15px 5px rgba(0, 0, 0, 0.3);
        }

        main table tbody {
            background-color: #f0f0f0;
            color: black;
        }

        tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        main table th,
        main table td {
            padding: 15px;
        }

        @media (max-width: 640px) {

            main table {
                width: 80vw;
                height: 95vh;
            }

            main table th,
            main table td {
                font-size: 10px;
                padding: 5px;
            }

            main table {
                height: 100px;
            }

            #container {
                margin-top: 50px;
            }
        }
    </style>
</head>

<body class="bg-cover bg-center bg-fixed bg-no-repeat w-screen" style="background-image: url('Images/main_bg2.png');">
    <main class="min-h-screen flex flex-col items-center justify-center py-40">
        <div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-green-500 via-green-600 to-white-800 z-30 h-22 w-screen">
            <div class="flex justify-between items-center">
                <img class="w-28 h-17" src="Images/logo.png" alt="Logo">
                <p class="text-white xl:text-5xl font-arial font-bold sm:text-2xl">East Gate Farm</p>
                <form action="logout.php" method="post" class="ml-2">

                    <button class="text-white bg-green-500 hover:bg-gray-600  focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-3.5 mr-2.5 text-center inline-flex items-center" type="button" data-dropdown-toggle="dropdown">Settings<svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg></button>

                    <!-- Dropdown menu -->
                    <div class="hidden bg-white text-base z-50 list-none divide-y divide-gray-100 rounded shadow my-4" id="dropdown">
                        <div class="px-4 py-3">
                            <span class="block text-sm">Sherwin Tajan</span>
                            <span class="block text-sm font-medium text-gray-900 truncate">Sherwintajan143@gmail.com</span>
                        </div>
                        <ul class="py-1" aria-labelledby="dropdown">
                            <li>
                            <li>
                                <a href="./log.php" class="text-sm hover:bg-gray-100 text-gray-700 block px-4 py-2">History & Log</a>
                            </li>
                            <button type="submit" class="text-sm hover:bg-gray-100 text-gray-700 block px-4 py-2">
                                Logout
                            </button>
                            </li>

                        </ul>
                    </div>
                    <!--  -->
                </form>
            </div>
        </div>
        <div class="max-w-screen-lg mx-auto px-10">
            <table id="sensor-table" class="mt-10 w-250 bg-white shadow-md rounded-lg overflow-hidden mx-auto">
                <thead class="bg-green-200 text-white" style="background-color: #047857;">
                    <tr>
                        <th class="py-1 px-4">Time</th>
                        <th class="py-1 px-4">Temp<br>(°C)</th>
                        <th class="py-1 px-4">Moisture<br>(%)</th>
                        <th class="py-1 px-4">Nutrient<br>Conductivity<br>(uS/cm)</th>
                        <th class="py-1 px-4">Water<br>Consumption<br>(L/m)</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-100 text-gray-700">
                    <?php foreach ($data as $row) : ?>
                        <tr>
                            <td class="py-1 px-4"><?php echo $row['timestamp']; ?></td>
                            <td class="py-1 px-4"><?php echo $row['temperature']; ?></td>
                            <td class="py-1 px-4"><?php echo $row['moisture']; ?></td>
                            <td class="py-1 px-4"><?php echo $row['conductivity']; ?></td>
                            <td class="py-1 px-4"><?php echo $row['flow']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function updateSensorData() {
            const tableBody = document.querySelector("#sensor-table tbody");
            fetch("dashboard-data.php")
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = "";
                    data.forEach(row => {
                        const newRow = document.createElement("tr");
                        const conductivityValue = parseFloat(row.conductivity);
                        let bgColor, textColor;

                        if (conductivityValue < 20000) {
                            bgColor = 'lightgreen';
                            textColor = 'black';
                        } else if (conductivityValue >= 20000 && conductivityValue <= 40000) {
                            bgColor = 'green';
                            textColor = 'white';
                        } else {
                            bgColor = 'red';
                            textColor = 'white';
                        }

                        newRow.innerHTML = `
                    <td>${row.timestamp}</td>
                    <td>${row.temperature} °C</td>
                    <td>${row.moisture}%</td>
                    <td style="background-color: ${bgColor}; color: ${textColor};">${row.conductivity} uS/cm</td>
                    <td>${row.flow} L/m</td>
                `;
                        tableBody.appendChild(newRow);

                    });
                })
                .catch(error => console.error(error));
        }

        setInterval(updateSensorData, 500);
        updateSensorData();
    </script>
    <script src="https://unpkg.com/@themesberg/flowbite@latest/dist/flowbite.bundle.js"></script>
</body>

</html>