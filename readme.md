# 🔍 Spryker Semantic Search Tool

The **Spryker Semantic Search Tool** enables intelligent, context-aware search across your Spryker project using Large Language Model (LLM)-based embeddings and [Chroma DB](https://www.trychroma.com/) for efficient indexing and retrieval.

## ✨ Features

- **Indexes key module APIs and configurations**, including:
    - `Facade` interfaces
    - `Client` interfaces
    - `Service` interfaces
    - `Plugin` interfaces and plugin classes
    - `Config` classes of modules

- **Semantic understanding of code**:
    - Uses class and method names
    - Incorporates method doc blocks for deeper intent and specification analysis
    - Does not use full file code

- **Efficient navigation and output**:
    - Presents results in **chunked format** for readability
    - Each result includes a **link to the source file and line number** for quick access

## 🧠 How It Works

1. **Embeddings Generation**: Creates vector embeddings for indexed elements using a Large Language Model (LLM).
2. **Indexing**: Stores embeddings in Chroma DB for fast semantic search.
3. **User Query**: Accepts natural language input.
4. **Matching & Ranking**: Finds semantically relevant matches across the project.
5. **Result Presentation**: Displays readable chunks with direct links to source files.

## 📦 Use Cases

- Understand large or unfamiliar Spryker codebases faster.
- Discover relevant module APIs and plugins by *use case*, not just name.
- Accelerate onboarding for new developers and cross-team collaboration.

---
> 💡 **Tip**: Works best when combined with up-to-date PHPDoc across modules for optimal semantic accuracy.

## 📦 Prerequisites

Ensure you have the following installed on your machine:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Composer](https://getcomposer.org/)
- Bash shell

---

## 🛠 Installation & Setup

1. Clone the repository into the project root directory:
```bash
   git clone git@github.com:vitaliiivanovspryker/spryker-project-semantic-search.git &&
   echo "/spryker-project-semantic-search/" >> .git/info/exclude &&
   cd spryker-project-semantic-search
````
2. Configure in `spryker-project-semantic-search/php/.env`

3. Run the installer script:

```bash
bash install
```

   This will:

    * Start Docker containers
    * Install dependencies via Composer
    * Pull ollama embedding model nomic-embed-text
    * Copy `.env.example` to `.env`
    * Index the project (takes 5–20 minutes)
    * Launch the interactive CLI tool

---

## 📂 Project Structure

```
├── spryker-project/
│      ├── src/
│      │    └── Pyz/
│      ├── ...
│      └── spryker-project-semantic-search/
│           ├── php/
│           │    └── bin/
│           │         └── sprykeye
│           ├── docker-compose.yml
│           ├── php.ini
│           ├── install
│           ├── run
│           └── readme.md
```

---

## 🧪 Usage

After setup, you can launch the search tool anytime by running:

```bash
docker exec -it php bash -c "bin/sprykeye project:search"
```
or
```bash
bash run
```

## 📄 License

MIT or your preferred license.

---

## 👥 Authors

* [Vitalii Ivanov](https://github.com/vitaliiivanovspryker)

```
