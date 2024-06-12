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
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logs</title>+
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Workbook/Excel Extensions -->
    <script src="https://cdn.rawgit.com/protobi/js-xlsx-style/v0.15.0/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx-populate/3.11.0/xlsx-populate.min.js"></script>
    <style>
        body {
            overflow: hidden;
        }

        #container {
            margin-top: 10px;
        }

        main table {
            text-align: center;
            border-collapse: collapse;
            box-shadow: 5px 5px 15px 5px rgba(0, 0, 0, 0.3);

        }

        main table tbody {
            display: block;
            height: 250px;
            /* Set the desired max-height for the tbody to enable scrolling */
            overflow-y: auto;
            /* Enable vertical scrolling */
        }

        main table tbody::-webkit-scrollbar {
            width: 0;
            /* Completely hide the scrollbar */
        }

        main table td,
        main table th {
            padding: 15px;
        }

        tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        #scroll::-webkit-scrollbar {
            display: none;
        }

        @media (max-width: 640px) {

            main table {
                width: 90vw;
                height: 95vh;
            }

            main table th,
            main table td {
                font-size: 10px;
                padding: 5px;
            }

            th {
                font-size: 5px;
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

<body class="bg-cover bg-center bg-fixed bg-no-repeat w-screen" style="background-image: url('Images/main_bg2.png   ');">
    <main class="min-h-screen flex flex-col items-center  justify-center py-8">
        <!-- Header -->
        <div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-green-500 via-green-600 to-white-800 z-30 h-22 w-screen">
            <div class="flex justify-between items-center ">
                <img class="w-28 h-17" src="Images/logo.png" alt="Logo">
                <p class="text-white xl:text-5xl font-arial font-bold sm:text-2xl">History & Logs</p>
                <form action="logout.php" method="post" class="ml-2">

                    <button class="text-white bg-green-500 hover:bg-gray-600  focus:ring-4 focus:ring-green-300 font-medium rounded-lg lg:text-sm px-2 py-3.5 mr-2.5 text-center inline-flex items-center" type="button" data-dropdown-toggle="dropdown">Settings<svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                                <a href="./index.php" class="text-sm hover:bg-gray-100 text-gray-700 block px-4 py-2 no-underline">Dashboard</a>
                            </li>
                            <li>
                                <a class="text-sm hover:bg-gray-100 text-gray-700 block px-4 py-2 no-underline" onclick="openModal()">Help</a>
                            </li>
                            <button type="submit" class="text-sm hover:bg-gray-100 text-gray-700 block px-4 py-2">
                                Logout
                            </button>
                        </ul>
                    </div>
                    <!--  -->
                </form>
            </div>
        </div>


        <!-- Date Filter -->
        <div id="container" class="flex flex-wrap text-black bg-green-500 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2.5 justify-center text-center inline-flex items-center">
            <label for="dateFilter" class="text-white ">Select Date:</label>
            <input type="date" id="dateFilter" class="py-1 px-4 mx-1">
            <select id="filterType" class="py-1 px-4">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
            <button onclick="filterData()" class="text-white mx-1 border-solid border-2 border-sky-500 p-2 text-black bg-green-500 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm">Filter</button>
            <button onclick="clearFilter()" class="text-white border-solid border-2 border-sky-500 p-2 text-black bg-green-500 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm sm:font-small">Clear</button>
            <button onclick="downloadFilteredData()" class="text-white ml-2 border-solid border-2 border-sky-500 p-2 text-black bg-green-500 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm">
                Download
            </button>

        </div>
        <!-- Table -->
        <div class="flex">
            <div class="max-w-screen-lg mx-auto px-5">
                <table id="sensorTable" class=" mt-3 lg:w-150 sm: bg-white shadow-md rounded-lg overflow-hidden mx-auto">
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
                                <td class="py-1 px-4 text-sm"><?php echo $row['timestamp']; ?></td>
                                <td class="py-1 px-4 text-sm"><?php echo $row['temperature']; ?></td>
                                <td class="py-1 px-4 text-sm"><?php echo $row['moisture']; ?></td>
                                <td class="py-1 px-4 text-sm"><?php echo $row['conductivity']; ?></td>
                                <td class="py-1 px-4 text-sm"><?php echo $row['flow']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr id="totalFlow" class="bg-gray-200 text-gray-700">
                            <td colspan="5">Total Flow Rate:</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>


    <!-- Help Modal -->
    <div class="fixed z-10 inset-0 overflow-y-auto hidden" id="myModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white w-full max-w-3xl rounded-lg shadow-lg">
                <div class="flex justify-between items-center border-b border-gray-200 p-4">
                    <h1 class="text-lg font-semibold">HELP</h1>
                    <button class="text-gray-500 hover:text-gray-700" onclick="closeModal()">Close</button>
                </div>
                <div class="p-4 h-80 overflow-y-auto" id="scroll" style="overflow: auto;">
                    <img class="w-full" />
                    <div class="p-4 flex-col h-full">
                        <p class="text-3xl font-semibold mb-4 tex">Conductivity Level:</p>
                        <div class="w-full h-full mr-2">
                            <div class="w-4 h-4 mr-2" style="background: lightgreen;"></div>
                            <span class="text-2xs">Less Conductivity</span>
                            <img src="./Images/Less.png" alt="Your Image" class="w-full" />
                            <br>
                            <p>If the reading is below 20000 the soil has not applicable to raise a plant , i'll recommend that use some organic soil and mix it with the soil, so that the plant can gather nutrients for it growths</p>
                        </div>
                        <hr>
                        <div class="flex items-center mt-5">
                            <div class="w-full h-full mr-2">
                                <div class="w-4 h-4 mr-2" style="background: green;"></div>
                                <span class="text-xl">Safe Conductivity</span>
                                <img src="./Images/Normal.png" alt="Your Image" class="w-full" />
                                <br>
                                <p>It gives the plant the right amount of nutrients that it needed, it has less sodium and chloride that can kill the plant.</p>
                            </div>
                        </div>
                        <hr>
                        <div class="flex items-center mt-5">
                            <div class="w-full h-full mr-2">
                                <div class="w-4 h-4 mr-2 " style="background: red;"></div>
                                <span class="text-xl">Danger Conductivity</span>
                                <img src="./Images/Danger.png" alt="Your Image" class="w-full" />
                                <br>
                                <p>When the conductivity gets higher or exceeds the 40000 reading it show that it can be the plant has been watered or if the reading didn't goes down it means the soil has a lot of element that plants can't absorb (e.g sodium and chloride).

                                <!-- TODO :: Change info 
                            
                            
                            -->
                                    <br>
                                    <br>
                                    PS: <u>if the reading didn't goes down it means the water has more salt than potassium , phosphorus , and nitrogen that can lead the plant dead.</u>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Modal Functionalities
        function openModal() {
            document.getElementById('myModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            document.getElementById('myModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function closeModalOnClickOutside(event) {
            if (event.target === document.getElementById('myModal')) {
                closeModal();
            }
        }

        //TODO :: EXCEL DOWNLOAD 

        // Tries 5x 

        // Function to download the data as an Excel file
        function downloadFilteredData() {
            const dateInput = document.getElementById('dateFilter').value;
            const filterType = document.getElementById('filterType').value;

            updateSensorData().then(data => {
                let filteredData = data;

                if (dateInput) {
                    if (filterType === 'daily') {
                        filteredData = data.filter(entry => entry.timestamp.includes(dateInput));
                    } else if (filterType === 'weekly') {
                        const weekStart = new Date(dateInput);
                        weekStart.setDate(weekStart.getDate() - weekStart.getDay());
                        const weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekStart.getDate() + 6);
                        filteredData = data.filter(entry => {
                            const timestampDate = new Date(entry.timestamp);
                            return timestampDate >= weekStart && timestampDate <= weekEnd;
                        });
                    } else if (filterType === 'monthly') {
                        filteredData = data.filter(entry => entry.timestamp.includes(dateInput.slice(0, 7)));
                    } else if (filterType === 'yearly') {
                        filteredData = data.filter(entry => entry.timestamp.includes(dateInput.slice(0, 4)));
                    }
                }

                // Add the total flow rate to the filtered data
                const totalFlow = updateTotalFlow(filteredData);
                const totalFlowRow = {
                    timestamp: 'Water Consumption',
                    temperature: '',
                    moisture: '',
                    conductivity: '',
                    flow: totalFlow
                };
                filteredData.push(totalFlowRow);

                // Create a worksheet
                const ws = XLSX.utils.json_to_sheet(filteredData);

                // Add colors to cells based on conductivity value
                filteredData.forEach((entry, index) => {
                    if (entry.conductivity !== undefined) {
                        const conductivityValue = parseFloat(entry.conductivity);

                        // Assuming 'conductivity' is in the 4th column
                        // TODO : FIX THIS BUG !!! ASAP !!!
                        const cellRef = XLSX.utils.encode_cell({
                            r: index + 1,
                            c: 3
                        });

                        if (conductivityValue < 20000) {
                            ws[cellRef].s = {
                                fill: {
                                    bgColor: {
                                        indexed: 42
                                    }
                                }
                            }; // Light Green
                        } else if (conductivityValue >= 20000 && conductivityValue <= 40000) {
                            ws[cellRef].s = {
                                fill: {
                                    bgColor: {
                                        indexed: 3
                                    }
                                }
                            }; // Green
                        } else {
                            ws[cellRef].s = {
                                fill: {
                                    bgColor: {
                                        indexed: 2
                                    }
                                }
                            }; // Red
                        }
                    }
                });

                // Create a workbook
                const wb = XLSX.utils.book_new();

                // Set the filename based on whether a date is selected
                const filename = dateInput ? `filteredData_${filterType}.xlsx` : 'alldata.xlsx';

                XLSX.utils.book_append_sheet(wb, ws, 'FilteredData');

                // Save the workbook
                XLSX.writeFile(wb, filename);
            });
        }

        // Getting Data
        // PS : it needed to be refreshed
        function updateSensorData() {
            return fetch("get-sensor-data.php")
                .then(response => response.json())
                .catch(console.error);
        }

        // Update the total water consumption 
        function updateTotalFlow(data) {
            return data.reduce((totalFlow, entry) => totalFlow + parseFloat(entry.flow), 0);
        }

        // Display water consumption
        function displayTotalFlow(totalFlow) {
            document.getElementById('totalFlow').innerHTML = `<td colspan="5">Total Flow Rate: ${totalFlow.toFixed(2)}</td>`;
        }

        // getting all the data and display it per cell of the table
        function displayDataInTable(data) {
            const tbody = document.querySelector('#sensorTable tbody');
            tbody.innerHTML = '';

            data.forEach(entry => {
                const row = document.createElement('tr');
                Object.keys(entry).forEach(key => {
                    if (key !== 'id') {
                        const cell = document.createElement('td');

                        // Add background color based on conductivity value
                        if (key === 'conductivity') {
                            const conductivityValue = parseFloat(entry[key]);

                            if (conductivityValue < 20000) {
                                cell.style.backgroundColor = 'lightgreen';
                                cell.style.color = "black";
                            } else if (conductivityValue >= 20000 && conductivityValue <= 40000) {
                                cell.style.backgroundColor = 'green';
                                cell.style.color = "white";
                            } else {
                                cell.style.backgroundColor = 'red';
                                cell.style.color = "white";
                            }
                        }

                        cell.textContent = entry[key];
                        row.appendChild(cell);
                    }
                });
                tbody.appendChild(row);
            });
            displayTotalFlow(updateTotalFlow(data));
        }

        // Date Filtering Function
        function filterData() {
            const dateInput = document.getElementById('dateFilter').value;
            const filterType = document.getElementById('filterType').value;

            updateSensorData().then(data => {
                let filteredData = data;

                if (dateInput) {
                    if (filterType === 'daily') {
                        filteredData = data.filter(entry => entry.timestamp.includes(dateInput));
                    } else if (filterType === 'weekly') {
                        const weekStart = new Date(dateInput);
                        weekStart.setDate(weekStart.getDate() - weekStart.getDay());
                        const weekEnd = new Date(weekStart);
                        weekEnd.setDate(weekStart.getDate() + 6);
                        filteredData = data.filter(entry => {
                            const timestampDate = new Date(entry.timestamp);
                            return timestampDate >= weekStart && timestampDate <= weekEnd;
                        });
                    } else if (filterType === 'monthly') {
                        filteredData = data.filter(entry => entry.timestamp.includes(dateInput.slice(0, 7)));
                    } else if (filterType === 'yearly') {
                        filteredData = data.filter(entry => entry.timestamp.includes(dateInput.slice(0, 4)));
                    }
                }

                displayDataInTable(filteredData);
            });
        }

        // Clear Date Filter
        function clearFilter() {
            document.getElementById('dateFilter').value = '';
            document.getElementById('filterType').value = 'daily';
            filterData();
        }
        // Updating Data 
        // PS : it needed to be refreshed
        updateSensorData().then(displayDataInTable);
    </script>
    <!-- Tailwind Script -->
    <script src="https://unpkg.com/@themesberg/flowbite@latest/dist/flowbite.bundle.js"></script>
</body>

</html>