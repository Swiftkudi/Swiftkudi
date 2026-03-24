import express from 'express';
import { IndexController } from './controllers/index';

const app = express();
const port = process.env.PORT || 3000;

// Middleware setup
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Initialize controllers
const indexController = new IndexController();

// Define routes
app.get('/', indexController.home);
app.get('/api', indexController.api);

// Start the server
app.listen(port, () => {
    console.log(`Server is running on http://localhost:${port}`);
});