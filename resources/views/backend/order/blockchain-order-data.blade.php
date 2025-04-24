<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockchain Data</title>
    <link rel="stylesheet" href="{{ asset('assets/css/blockchain.scss') }}">

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    

body {
    font-family: Arial, sans-serif;
    background-color:#000;
    background-position: center;
    color: #fff; /* Change text color to white */
    position: relative;
}

.animation-background {
    position: absolute; /* Position the SVG absolutely within the body */
    top: 0;
    left: 0;

}

.card {
    background: rgba(0, 0, 0, 0.8);
    border: 1px solid #ccc;
    border-radius: 10px;
    width: 1000px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin: 0 auto;
    color: rgba(255, 255, 255, 0.8);
    text-shadow: 1px 1px 1px #000;
    position: relative; /* Make the card a relative container for absolute positioning */
    z-index: 1; /* Place it above the animation */
}
    
    header {
        background-color: #007bff;
        color: #fff;
        text-align: center;
        padding: 20px 0;
    }
    
    main {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    
   
    /* Add this CSS to style the tables */
.outer-table {
    width: 100%;
}

.inner-table {
    width: 100%;
    border: none; /* Remove border for inner tables */
}

    .card-header {
        background-color: #007bff;
        color: #fff;
        padding: 10px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    
    .card-body {
        padding: 20px;
    }
    
    table {
        width: 100%;
    }
    
    table td {
        padding: 10px;
        border-bottom: 1px solid #ccc;
    }
    
    table td:first-child {
        font-weight: bold;
        width: 40%;
    }

    .scrollable-table {
    max-height: 300px; /* Adjust the maximum height as needed */
    overflow: auto;
}
    
</style>
</head>
<body >
    <header>
        <h1>Block Hash: {{ $data['hash_id'] }}</h1>
    </header>
   
    <main>
        <div class="card">
            <div class="card-header">Order Details</div>
            <div class="card-body">
                <table>
                    <tbody>
                        <tr>
                            <td>Order ID:</td>
                            <td>{{ $data['orderID'] }}</td>
                        </tr>
                        <tr>
                            <td>Order Details:</td>
                            <td>
                                <div class="scrollable-table">
                                <table border="1" class="outer-table">
                                    <?php
                                    foreach ($orderDetail as $key => $value) {
                                        if (($value == null) || $value==  '') {
                                           continue;
                                        }
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($key) . '</td>';
                                        echo '<td>';
                                        
                                        if (is_array($value)) {
                                            echo '<table border="1" class="inner-table">';
                                            foreach ($value as $subKey => $subValue) {
                                                echo '<tr>';
                                                echo '<td>' . htmlspecialchars($subKey) . '</td>';
                                                echo '<td>' . htmlspecialchars($subValue) . '</td>';
                                                echo '</tr>';
                                            }
                                            echo '</table>';
                                        } else {
                                            echo htmlspecialchars($value);
                                        }
                                        
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </table>
                                </div>
                            </td>
                        </tr>
                        
                        
                        <tr>
                            <td>Status:</td>
                            <td>{{ $data['status'] }}</td>
                        </tr>
                        <tr>
                            <td>Address Short Code:</td>
                            <td>{{ $data['address_short_code'] }}</td>
                        </tr>
                        <tr>
                            <td>Address (Full):</td>
                            <td>{{ $data['address_f'] }}</td>
                        </tr>
                        <tr>
                            <td>Hash ID:</td>
                            <td>{{ $data['hash_id'] }}</td>
                        </tr>
                        <tr>
                            <td>Block Number:</td>
                            <td>{{ $data['block_number'] }}</td>
                        </tr>
                        <tr>
                            <td>To Address:</td>
                            <td>{{ $data['to_address'] }}</td>
                        </tr>
                        <tr>
                            <td>Movement:</td>
                            <td>{{ $data['movement'] }}</td>
                        </tr>
                        <tr>
                            <td>Created Date:</td>
                            <td>{{ $data['created_date'] }}</td>
                        </tr>
                        <tr>
                            <td>Updated Date:</td>
                            <td>{{ $data['updated_date'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
