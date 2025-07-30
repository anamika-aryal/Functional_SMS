<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <style>
        /* Reset Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* Body with gradient background */
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #F9F5F6, #F8E8EE, #FDCEDF, #F2BED1);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        /* Gradient animation */
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* Glassmorphic container */
        .container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            padding: 40px 60px;
            text-align: center;
            max-width: 600px;
            animation: fadeIn 1.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .container h1 {
            font-size: 2.5rem;
            color: #d2649a;
            margin-bottom: 10px;
        }

        .container p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 20px;
        }

        /* Creator names */
        .creators {
            font-size: 1rem;
            color: #6d4c71;
            margin-top: 10px;
            margin-bottom: 30px;
            font-style: italic;
        }

        /* Button Style */
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(90deg, #FDCEDF, #F2BED1);
            color: #333;
            text-decoration: none;
            font-weight: bold;
            border-radius: 30px;
            transition: 0.3s ease;
        }

        .btn:hover {
            background: linear-gradient(90deg, #F2BED1, #F8E8EE);
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the<br>Student Management System</h1>
        <p>Manage Students, Teachers, Courses, and More Seamlessly!</p>
        <div class="creators">
            Created by: Animika, Suraj, Anjana, Anshika
        </div>
        <a href="login.php" class="btn">Go to Login</a>
    </div>
</body>
</html>