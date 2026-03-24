#!/bin/bash

# This script sets up the EarnDesk project environment

# Update package list
echo "Updating package list..."
sudo apt-get update

# Install Node.js and npm if not already installed
if ! command -v node &> /dev/null
then
    echo "Node.js not found. Installing Node.js and npm..."
    sudo apt-get install -y nodejs npm
else
    echo "Node.js is already installed."
fi

# Install project dependencies
echo "Installing project dependencies..."
npm install

# Additional setup commands can be added here

echo "Setup complete!"