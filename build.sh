#!/bin/bash

# Build script for deployment
echo "Installing Node dependencies..."
npm install

echo "Building application..."
npm run build

echo "Build completed successfully!"