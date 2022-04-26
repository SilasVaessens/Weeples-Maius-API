import fetch from 'node-fetch';
import express, { json } from 'express';
const app = express();
const PORT = 8080;

var missingData = [];
var error = '';

app.use(json());

app.listen(
    PORT,
    () => console.log("Weeples-Maius API started")
);

app.post('/maius', async (req, res) => {
    if (!req.body.description) {
        missingData.push('beschrijving');
    }
    if (!req.body.datetime) {
        missingData.push('unix-tijd');
    }
    if (!req.body.location) {
        missingData.push('locatiegegevens (coördinaten of geohash en naam locatie)');
    }
    if (req.body.location && !req.body.location.geohash && (!req.body.location.coordinates || !req.body.location.coordinates.latitude || !req.body.location.coordinates.longitude)) {
        missingData.push('coördinaten of geohash');
    }
    if (req.body.location && !req.body.location.name) { 
        missingData.push('naam locatie');
    }
    if (!req.body.name) {
        missingData.push('naam activiteit');

    }
    if (missingData.length == 0){
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
    else if (missingData.length > 0){
        missingData.forEach((data, index) => {
            if (index == 0){
                data = data.charAt(0).toUpperCase() + data.slice(1);
            }
            if (index == missingData.length - 2){
                error = error + data + ' en ';
            }
            else {
                error = error + data + ', ';
            }
        });
        error = error.slice(0, -2);
        if (missingData.length === 1){
            error = error + ' is vereist voor het aanmaken van een activiteit op Weeples';
        }
        else {
            error = error + ' zijn vereist voor het aanmaken van een activiteit op Weeples';
        }
        res.status(400).send({message: error});
        error = '';
        missingData = [];
    }
});