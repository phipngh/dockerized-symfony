# Symfony - Firebase - Docker to start

## Prerequisite

1. Docker
1. [Docker Compose](https://docs.docker.com/compose/install/)
1. Make command
1. Firebase

## Firebase Config

1. Create new [Firebase](https://console.firebase.google.com/) project
1. Generate new private key in Project settings/Service accounts
1. Create new collection and set collection name in .env file
1. Copy credentials in json file you just created and paste to file ./config/credentials.json

## Installation

1. Run `make infra-up && make install`

## Usage

1. Open `http://localhost:8080/` in your favorite web browser
