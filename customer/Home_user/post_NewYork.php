<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New York Adventures</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }

        header {
        background: linear-gradient(to bottom, #ffecd2, #fcb69f);
        color: #fff;
        text-align: center;
        padding: 50px 0;
        position: relative;
        }

    header h1 {
        margin: 0;
        font-size: 48px;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    header img {
        margin-top: 20px;
        width: 90%;
        max-width: 1000px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    header .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 10px;
        z-index: 1;
    }

    header h1 {
        position: relative;
        z-index: 2;
    }

        .post {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .post img {
            width: 100%;
            border-radius: 5px;
        }

        .post h2 {
            color: #333;
        }

        .post p {
            color: #666;
        }

        .recommendation {
            font-weight: bold;
            color: #0779e4;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: linear-gradient(to bottom, #ffecd2,rgb(148, 149, 239));
            color: #333;
            position: relative;
        }

    </style>
</head>
<body>
    <header>
        <div class="overlay"></div>
            <h1>New York Adventures</h1>
        <img src="./image/newyorkadventureclub.jpg" alt="paris">
    </header>

    <div class="container">
        <div class="post">
            <h2>1. Statue of Liberty</h2>
            <img src="./image/Statue of Liberty.jpg" alt="Statue of Liberty">
            <p>The Statue of Liberty, located on Liberty Island in New York Harbor, is one of the most iconic symbols of freedom and democracy. Gifted by France to the United States in 1886, it represents hope and opportunity for millions of immigrants who arrived in America. Standing at 305 feet tall, it depicts the Roman goddess Libertas holding a torch and a tablet inscribed with the date of the Declaration of Independence. The statue is a UNESCO World Heritage Site and a must-visit landmark.</p>
            <p class="recommendation">Recommendation: Book tickets early to access the crown for a unique view.</p>
        </div>

        <div class="post">
            <h2>2. Central Park</h2>
            <img src="./image/Central Park.jpg" alt="Central Park">
            <p>Central Park, situated in the heart of Manhattan, is a sprawling urban oasis spanning 843 acres. Opened in 1858, it offers a peaceful retreat from the hustle and bustle of New York City. The park features scenic landscapes, lakes, walking trails, and attractions such as the Central Park Zoo, Bethesda Terrace, and Strawberry Fields. It's a favorite spot for recreation, picnics, and cultural events, attracting millions of visitors annually.</p>
            <p class="recommendation">Recommendation: Visit during fall to enjoy the colorful foliage.</p>
        </div>

        <div class="post">
            <h2>3. Times Square</h2>
            <img src="./image/Times Square.jpg" alt="Times Square">
            <p>Times Square, located at the intersection of Broadway and Seventh Avenue in Midtown Manhattan, is a dazzling spectacle of lights, entertainment, and energy. Known as "The Crossroads of the World," it is a global icon of New York City and a hub of commerce and culture. Its giant electronic billboards, illuminated 24/7, make it one of the most photographed places in the world. Times Square is the epicenter of Broadway theater, home to dozens of world-class productions, and hosts the world-famous New Year's Eve Ball Drop, an event watched by millions globally. The area also features flagship stores, renowned restaurants, and entertainment venues. With its vibrant atmosphere, Times Square captures the dynamic spirit of New York and continues to draw millions of visitors each year.</p>
            <p class="recommendation">Recommendation: Experience the vibrant nightlife and grab a photo in front of the iconic billboards.</p>
        </div>

        <div class="post">
            <h2>4. Brooklyn Bridge</h2>
            <img src="./image/Brooklyn Bridge.jpg" alt="Brooklyn Bridge">
            <p>The Brooklyn Bridge, completed in 1883, is a historic suspension bridge that connects the boroughs of Manhattan and Brooklyn over the East River. Designed by John A. Roebling and completed by his son Washington Roebling, the bridge was a groundbreaking engineering feat of its time, using steel-wire cables for the first time in history. The bridge spans 1,595 feet and features iconic Gothic-style towers made of limestone and granite. It serves as a vital transportation link and offers a pedestrian walkway that provides breathtaking views of the New York City skyline, the East River, and landmarks such as the Statue of Liberty. A walk across the Brooklyn Bridge is not just a journey between two boroughs but a step back in history, symbolizing innovation, resilience, and the connection between people. The bridge remains a timeless symbol of New York City and an architectural masterpiece admired worldwide.</p>
            <p class="recommendation">Recommendation: Walk the bridge at sunset for a breathtaking experience.</p>
        </div>
    </div>

    <footer class="footer">
        <p>Discover more at our Travel Blog!</p>
        <nav>
                <a href="index_homepage.php">HOME PAGE</a>
        </nav>
    </footer>
</body>
</html>
