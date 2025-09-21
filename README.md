# Academic Research Assistant - AI Chatbot

**Final Project: LLM-Based Tools and Gemini API Integration for Data Scientists**

A sophisticated AI-powered academic research assistant built with Laravel and Google Gemini API, designed to support students and researchers throughout their academic journey.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Integration](#api-integration)
- [Architecture](#architecture)
- [Screenshots](#screenshots)
- [Security](#security)
- [Future Enhancements](#future-enhancements)
- [Contributing](#contributing)

## 🎯 Overview

The Academic Research Assistant is an intelligent chatbot designed to provide comprehensive support for academic research activities. It leverages Google's Gemini AI to offer specialized assistance across different research phases, from initial brainstorming to final analysis.

### Use Case
This application addresses the common challenges faced by students and researchers:
- Generating and refining research ideas
- Understanding complex methodologies
- Getting guidance on data analysis
- Improving academic writing
- Conducting literature reviews

## ✨ Features

### Core Functionality
- **Multi-Mode Conversations**: Specialized AI assistance for different research needs
- **Real-time Chat Interface**: Seamless interaction with AI assistant
- **Academic Templates**: Quick-start guides for common research tasks
- **Export Functionality**: Download conversation history for reference
- **Service Monitoring**: Real-time API health and status tracking

### AI Conversation Modes
1. **General Assistant**: Broad academic support and guidance
2. **Research Brainstorm**: Creative idea generation with high creativity parameters
3. **Data Analysis**: Statistical analysis help with precision-focused responses
4. **Academic Writing**: Structure and style improvement assistance  
5. **Literature Review**: Research synthesis and gap identification

### Advanced Features
- Session-based conversation memory
- Context-aware responses
- Dynamic suggestion system
- Professional responsive design
- Cross-platform compatibility

## 🛠 Technology Stack

### Backend
- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **AI Service**: Google Gemini API (gemini-1.5-flash)
- **Session Storage**: File-based sessions
- **Validation**: Laravel Form Requests

### Frontend
- **CSS Framework**: Bootstrap 5.3.0
- **Icons**: Font Awesome 6.4.0
- **JavaScript**: Vanilla ES6+
- **Styling**: Custom CSS with glassmorphism effects

### Development Tools
- **Server**: XAMPP/Laravel Artisan
- **Version Control**: Git & GitHub
- **API Client**: Laravel HTTP Client
- **Environment**: Windows/Linux/macOS compatible

## 🚀 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js (optional, for asset compilation)
- Google Gemini API key

### Step-by-Step Setup

1. **Clone the repository**
```bash
git clone [your-repository-url]
cd academic-research-assistant
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure environment variables**
```env
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-1.5-flash
GEMINI_TIMEOUT=60
```

5. **Start the development server**
```bash
php artisan serve
```

6. **Access the application**
```
http://127.0.0.1:8000
```

## ⚙️ Configuration

### Gemini API Setup

1. **Get API Key**
   - Visit [Google AI Studio](https://aistudio.google.com/)
   - Create a new API key
   - Copy the key to your `.env` file

2. **API Configuration**
   - Model: `gemini-1.5-flash` (free tier)
   - Rate limits: 15 requests/minute, 1,500/day
   - Context window: Up to 4,096 tokens per conversation

3. **Custom Parameters**
```php
// config/gemini.php
'temperature' => 0.7,        // Creativity level
'max_tokens' => 1024,        // Response length
'top_p' => 0.95,            // Nucleus sampling
'top_k' => 40,              // Top-k sampling
```

### Mode-Specific Settings
- **Brainstorm Mode**: Temperature 0.9 (high creativity)
- **Analysis Mode**: Temperature 0.3 (focused precision)
- **Writing Mode**: Temperature 0.6 (balanced assistance)
- **Literature Mode**: Temperature 0.4 (structured synthesis)

## 📖 Usage

### Basic Usage

1. **Start a Conversation**
   - Open the application in your browser
   - Select an appropriate mode from the dropdown
   - Type your question or use a template

2. **Mode Selection Guide**
   - **General**: Broad questions, mixed topics
   - **Brainstorm**: Generating ideas, creative thinking
   - **Analysis**: Data interpretation, statistical help
   - **Writing**: Structure, style, citations
   - **Literature**: Paper reviews, synthesis

3. **Template Usage**
   - Click on research templates for quick starts
   - Templates provide structured prompts
   - Customizable for specific research needs

### Advanced Features

**Export Conversations**
```
Click the download icon → Saves as .txt file
Includes timestamps and mode information
```

**Clear History**
```
Click trash icon → Confirms deletion
Resets session but keeps templates
```

**Service Monitoring**
```
Green dot: API connected and ready
Red dot: Service unavailable
Yellow dot: Connection issues
```

## 🔗 API Integration

### Gemini API Implementation

The application uses a custom service class to handle Google Gemini API interactions:

```php
class GeminiService
{
    // Core methods
    public function generateChatResponse($message, $mode)
    public function isServiceRunning()
    public function getAvailableModels()
    public function getServiceHealth()
}
```

### Request Flow
1. User input validation
2. Mode-based prompt engineering
3. API request with optimized parameters
4. Response processing and formatting
5. Session storage and UI update

### Error Handling
- Connection timeouts: 60-second limit
- API errors: Graceful degradation
- Invalid responses: User-friendly messages
- Rate limiting: Automatic detection

## 🏗 Architecture

### MVC Pattern
```
app/
├── Http/Controllers/
│   └── ContentGeneratorController.php
├── Services/
│   └── GeminiService.php
└── Models/ (session-based, no database)

resources/views/
└── generator/
    └── index.blade.php

routes/
└── web.php
```

### Key Components

**Controller Layer**
- Request validation
- Service orchestration
- Response formatting
- Session management

**Service Layer**
- API communication
- Prompt engineering
- Parameter optimization
- Health monitoring

**View Layer**
- Responsive UI
- Real-time updates
- Error handling
- User interactions

## 📸 Screenshots

### Main Interface
![Main Interface](screenshots/main-interface.png)
*Clean, professional chat interface with mode selection*

### Conversation Modes
![Conversation Modes](screenshots/modes-demo.png)
*Different AI personalities for specialized assistance*

### Research Templates
![Templates](screenshots/templates.png)
*Quick-start templates for common research tasks*

### Export Feature
![Export](screenshots/export-feature.png)
*Download conversation history for reference*

### Mobile Responsive
![Mobile View](screenshots/mobile-responsive.png)
*Optimized for all device sizes*

## 🔒 Security

### Implemented Security Measures

**API Key Protection**
- Environment variables only
- Never committed to version control
- Server-side processing only

**Input Validation**
- Laravel form validation
- XSS prevention
- CSRF protection enabled

**Session Security**
- Secure session configuration
- Limited conversation history
- No persistent user data

**Error Handling**
- Generic error messages
- No sensitive information exposure
- Comprehensive logging

### Security Best Practices
```bash
# Verify API key security
git log --all --full-history -- .env  # Should be empty
grep -r "AIzaSy" . --exclude-dir=vendor  # Only in .env
```

## 🚧 Future Enhancements

### Planned Features
- **User Authentication**: Multi-user support with persistent history
- **File Upload**: PDF analysis and document processing
- **Advanced Analytics**: Usage statistics and insights
- **Plugin System**: Extensible functionality framework

### Technical Improvements
- **Rate Limiting**: Request throttling implementation
- **Caching**: Response caching for common queries
- **Database Integration**: Persistent conversation storage
- **API Versioning**: Support for multiple AI models

### UI/UX Enhancements
- **Dark Mode**: Theme switching capability
- **Voice Input**: Speech-to-text integration
- **Rich Text**: Markdown rendering in responses
- **Collaboration**: Shared conversations feature

## 🤝 Contributing

This project is primarily for academic demonstration. However, contributions are welcome:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📝 Academic Context

### Learning Outcomes Demonstrated
- **AI Integration**: Practical implementation of LLM APIs
- **Full-Stack Development**: Complete web application
- **User Experience Design**: Professional interface design
- **Software Architecture**: Clean, maintainable code structure

### Technical Skills Showcased
- RESTful API integration
- Modern PHP frameworks
- Responsive web design
- Security best practices
- Documentation standards

## 📄 License

This project is created for academic purposes as part of a final project submission.

## 📧 Contact

**Project Author**: Farhan Septian
**Course**: LLM-Based Tools and Gemini API Integration
**Institution**: [Your University]
**Submission Date**: 21-09-2025

---

*This project demonstrates the practical application of AI technologies in academic research support, showcasing both technical implementation skills and understanding of user-centered design principles.*

# Academic Project Notice
This repository is temporarily public for academic evaluation purposes only.
Please do not fork or clone for commercial use.