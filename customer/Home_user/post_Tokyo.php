<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tokyo the capital of Japan</title>
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
            <h1>Tokyo: The capital of Japan, known for its modernity.</h1>
        <img src="./image/tokyo_pic.jpeg" alt="tokyo">
    </header>

    <div class="container">
        <div class="post">
            <h2>1. Meiji Shrine</h2>
            <img src="./image/Meiji Shrine.jpg" alt="Meiji Shrine">
            <p>Meiji Shrine (Meiji Jingu) was established in 1920 and dedicated to the deified Emperor Meiji and Empress Shoken. One of the most visited shrines on New Year's Day, the shrine often receives thousands of devotees praying for success in love, exams and business. After passing through the shrine's entrance, you'll be greeted with views od massive oak and camphor trees as you walk towards the main sanctuary building (honden). Here, you should bow once before passing through the large sacred gate (otorii), which was reconstructed in 1975. </p>
            <p class="recommendation">Recommendation: To make a wish, bow and clap your hands twice at the main sanctuary building. You can also take and interpret unique fortune slips (omikuji), which are inscribed with waka poems. The Imperial Garden (gyoen) at the southern end of Meiji Shrine has around 1,500 irises that usually bloom in June. There are also 100,000 trees within its grounds, making this spiritual hotspot is a great place to unwind in Tokyo. </p>
        </div>

        <div class="post">
            <h2>2. Nakamise, Asakusa</h2>
            <img src="./image/nakamise.jpg" alt="Nakamise">
            <p>With a history stretching back 300 years, Nakamise in Asakusa is one of the oldest shopping streets in Japan. The row of shops starts from Kaminari-mon Gate – known for its huge 4-metre-long hanging red lantern – and continues for 250 metres to Sensoji, the oldest temple in Japan. On both sides of the street, there are nearly 90 shops and stalls selling regional foods like doll cake (ningyo-yaki), steamed yeast buns (manju) and rice crackers (senbei), as well as Japanese souvenirs such as chopsticks, umbrellas and postcards.</p>
            <p class="recommendation">Recommendation: There are festivals and events taking place in Asakusa almost every month. Sanjyamatsuri Festival in May, which has been held for more than 700 years, sees several portable shrines (mikoshi) paraded around the streets of Nakamise.</p>
        </div>

        <div class="post">
            <h2>3. Tokyo Skytree</h2>
            <img src="./image/skytree.jpg" alt="Tokyo Skytree">
            <p>Tokyo Skytree is a radio tower that's around 634 metres in height, with a 350-metre-high observation deck offering excellent views over the Kanto Plain. Completed in 2012, it was constructed in the Oshiage area of Sumida ward to support digital terrestrial broadcasting. </p>
            <p>From the observation deck floor, you can take a TEMBO Shuttle, which has a see-through ceiling, to the 450-metre-high Tembo Galleria observation corridor. After stepping out from the shuttle, you can walk to Sorakara Point, Tokyo Skytree's highest accessible point. On the ground level, Tokyo Skytree Town offers facilities such as Sumida Aquarium, Planetarium TENKU, as well as several cafés and restaurants.</p>
            <p class="recommendation">Recommendation: Exploring the cafes and restaurants in this place is the great experience.</p>
        </div>

        <div class="post">
            <h2>4. Shinjuku Gyoen</h2>
            <img src="./image/shinjuku.jpg" alt="Shinjuku Gyoen">
            <p>Shinjuku Gyoen was established as Japan’s first imperial garden in 1906. It’s a landscaped garden typical of the Meiji period. Within the surrounding area of 3.5 sq km, you'll find around 100,000 trees and various flowers from all 4 seasons – cherry blossom in springtime, hydrangeas in summer, cluster-amaryllis and osmanthus in autumn, and Japanese daffodils and camellia in winter. </p>
            <p class="recommendation">Recommendation: You can also observe tropical flowers and fruits in Shinjuku Gyoen's onsite greenhouse. There are cafés, resting houses and tearooms as well, allowing you to enjoy a relaxing walk around this green oasis. Shinjuku Gyoen is within a 10-minute walk from Shinjuku Station.</p>
        </div>
    </div>

    <footer class="footer">
        <p>Discover more at our Let's Travel!</p>
        <nav>
                <a href="index_homepage.php">HOME PAGE</a>
        </nav>
    </footer>
</body>
</html>
