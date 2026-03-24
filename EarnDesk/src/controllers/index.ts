class IndexController {
    public async getIndex(req, res) {
        res.send('Welcome to the EarnDesk API');
    }

    public async getStatus(req, res) {
        res.send({ status: 'API is running' });
    }

    // Add more methods for handling other routes as needed
}

export default IndexController;