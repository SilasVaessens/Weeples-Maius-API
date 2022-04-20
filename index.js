import fetch from 'node-fetch';
import express, { json } from 'express';
const app = express();
const PORT = 8080;

app.use(json());

app.listen(
    PORT,
    () => console.log(`it's alive on http://localhost:${PORT}`)
)

app.post('/maius', async (req, res) => {
    if (!req.body.description) {
        res.status(400).send({message: 'Beschrijving van de activiteit vereist voor het aanmaken van een activiteit op Weeples'});
    }
    else if (!req.body.datetime) {
        res.status(400).send({message: 'Unix-tijd van wanneer de activiteit plaatsvindt vereist voor het aanmaken van een activiteit op Weeples'});
    }
    else if (!req.body.location) {
        res.status(400).send({message: 'Locatiegegevens bestaande uit een naam en coördinaten of de geohash van de locatie van de activiteit zijn vereist voor het aanmaken van een activiteit op Weeples'});
    }
    else if (!req.body.location.geohash && (!req.body.location.coordinates || !req.body.location.coordinates.latitude || !req.body.location.coordinates.longitude)) {
        res.status(400).send({message: 'Coördinaten of de geohash van de locatie van de activiteit zijn vereist voor het aanmaken van een activiteit op Weeples'});
    }
    else if (!req.body.location.name) {
        res.status(400).send({message: 'Naam van de locatie van de activiteit vereist voor het aanmaken van een activiteit op Weeples'});
    }
    else if (!req.body.name) {
        res.status(400).send({message: 'Naam van de activiteit vereist voor het aanmaken van een activiteit op Weeples'});
    }
    else {
        console.log(JSON.stringify(req.body, null, 4));

        const response = await fetch('https://jsonplaceholder.typicode.com/posts', {
            method: 'POST',
            body: JSON.stringify(req.body),
            headers: {'Content-type': 'application/json'}
        })
        if (response.ok) {
            const data = await response.json();
            res.status(201).send({message: "Activieit op Weeples aangemaakt", activity: data});
        }
        else {
            res.status(500).send({message: `Error ${response.status}: ${response.statusText}`})
        }
    }
})