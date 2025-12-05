
const sampleManga = [
    {
        id: 1,
        title: 'One Piece',
        cover: 'images.jpg',
        chapters: ['Chapter 1', 'Chapter 2', 'Chapter 3'],
        genre: ['Action', 'Adventure', 'Comedy'],
        description: 'Follow Monkey D. Luffy and his pirate crew in their search for the ultimate treasure, the One Piece.'
    },
    {
        id: 2,
        title: 'Naruto',
        cover: 'p.jpg',
        chapters: ['Chapter 1', 'Chapter 2', 'Chapter 3'],
        genre: ['Action', 'Adventure', 'Fantasy'],
        description: 'Follow Naruto Uzumaki, a young ninja with a sealed demon within him, on his journey to become the leader of his village.'
    },
    {
        id: 3,
        title: 'Demon Slayer',
        cover: 'dm.jpg',
        chapters: ['Chapter 1', 'Chapter 2', 'Chapter 3'],
        genre: ['Action', 'Drama', 'Fantasy'],
        description: 'Tanjiro Kamado sets out to become a demon slayer after his family is slaughtered and his sister turned into a demon.'
    }
];


const mangaGrid = document.getElementById('mangaGrid');
const mangaViewer = document.getElementById('mangaViewer');
const searchInput = document.getElementById('searchInput');
const homeBtn = document.getElementById('homeBtn');
const bookmarksBtn = document.getElementById('bookmarksBtn');
const darkModeBtn = document.getElementById('darkModeBtn');
const prevPageBtn = document.getElementById('prevPage');
const nextPageBtn = document.getElementById('nextPage');
const pageInfo = document.getElementById('pageInfo');
const mangaPage = document.getElementById('mangaPage');
const loadingOverlay = document.getElementById('loadingOverlay');
const mangaTitle = document.getElementById('mangaTitle');
const chapterSelect = document.getElementById('chapterSelect');
const bookmarkBtn = document.getElementById('bookmarkBtn');
const viewButtons = document.querySelectorAll('.view-btn');
const categoryTags = document.querySelectorAll('.category-tag');


let currentManga = null;
let currentChapter = null;
let currentPage = 1;
let bookmarks = JSON.parse(localStorage.getItem('bookmarks')) || [];
let currentView = 'grid';
let isDarkMode = localStorage.getItem('darkMode') === 'true';


const typingTexts = [
    "Manga Reader",
    "Best Manga Site",
    "Your Manga Library",
    "読み放題"
];

let textIndex = 0;
let charIndex = 0;
let isDeleting = false;
let isWaiting = false;

function typeText() {
    const dynamicText = document.querySelector('.dynamic-text');
    const currentText = typingTexts[textIndex];
    const waitTime = isWaiting ? 2000 : isDeleting ? 50 : 100;

    if (!isDeleting && !isWaiting && charIndex < currentText.length) {
        
        dynamicText.textContent = currentText.substring(0, charIndex + 1);
        charIndex++;
        setTimeout(typeText, waitTime);
    } else if (!isDeleting && !isWaiting && charIndex === currentText.length) {
        
        isWaiting = true;
        setTimeout(typeText, waitTime);
    } else if (isWaiting) {
       
        isWaiting = false;
        isDeleting = true;
        setTimeout(typeText, waitTime);
    } else if (isDeleting && charIndex > 0) {
        
        dynamicText.textContent = currentText.substring(0, charIndex - 1);
        charIndex--;
        setTimeout(typeText, waitTime);
    } else if (isDeleting && charIndex === 0) {
      
        isDeleting = false;
        textIndex = (textIndex + 1) % typingTexts.length;
        setTimeout(typeText, waitTime);
    }
}


function init() {
    displayMangaGrid(sampleManga);
    setupEventListeners();
    setupDarkMode();
    typeText(); 
}


function setupDarkMode() {
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
        darkModeBtn.innerHTML = '<i class="fas fa-sun"></i><span>Light Mode</span>';
    }
}


function toggleDarkMode() {
    isDarkMode = !isDarkMode;
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
    darkModeBtn.innerHTML = isDarkMode ? 
        '<i class="fas fa-sun"></i><span>Light Mode</span>' : 
        '<i class="fas fa-moon"></i><span>Dark Mode</span>';
}


function displayMangaGrid(mangaList) {
    showLoading();
    mangaGrid.innerHTML = '';
    mangaList.forEach(manga => {
        const card = createMangaCard(manga);
        mangaGrid.appendChild(card);
    });
    hideLoading();
}


function createMangaCard(manga) {
    const card = document.createElement('div');
    card.className = `manga-card ${currentView === 'list' ? 'list-view' : ''}`;
    const isBookmarked = bookmarks.includes(manga.id);
    
    card.innerHTML = `
        <img src="${manga.cover}" alt="${manga.title}" onerror="this.src='placeholder.jpg'">
        <div class="manga-info">
            <h3>${manga.title}</h3>
            <p>${manga.chapters.length} Chapters</p>
            <p class="manga-genres">${manga.genre.join(', ')}</p>
            <p class="manga-description">${manga.description}</p>
            <button class="bookmark-btn ${isBookmarked ? 'active' : ''}" onclick="toggleBookmark(event, ${manga.id})">
                <i class="fa${isBookmarked ? 's' : 'r'} fa-bookmark"></i>
            </button>
        </div>
    `;
    
    card.addEventListener('click', (e) => {
        if (!e.target.closest('.bookmark-btn')) {
            openManga(manga);
        }
    });
    
    return card;
}


function toggleBookmark(event, mangaId) {
    event.stopPropagation();
    const index = bookmarks.indexOf(mangaId);
    
    if (index === -1) {
        bookmarks.push(mangaId);
    } else {
        bookmarks.splice(index, 1);
    }
    
    localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
    displayMangaGrid(currentView === 'bookmarks' ? getBookmarkedManga() : sampleManga);
}


function getBookmarkedManga() {
    return sampleManga.filter(manga => bookmarks.includes(manga.id));
}


function openManga(manga) {
    showLoading();
    currentManga = manga;
    currentChapter = manga.chapters[0];
    currentPage = 1;
    
    
    chapterSelect.innerHTML = `
        <option value="">Pilih Chapter</option>
        ${manga.chapters.map((chapter, index) => `
            <option value="${index}">${chapter}</option>
        `).join('')}
    `;
    
    mangaTitle.textContent = manga.title;
    updateViewer();
    mangaGrid.style.display = 'none';
    mangaViewer.style.display = 'block';
    hideLoading();
}


function updateViewer() {
    showLoading();
    
    mangaPage.src = `placeholder.jpg`;
    pageInfo.textContent = `Chapter ${currentChapter} - Page ${currentPage}`;
    
   
    const isBookmarked = bookmarks.includes(currentManga.id);
    bookmarkBtn.innerHTML = `<i class="fa${isBookmarked ? 's' : 'r'} fa-bookmark"></i>`;
    hideLoading();
}


function setupEventListeners() {
    searchInput.addEventListener('input', handleSearch);
    homeBtn.addEventListener('click', showHome);
    bookmarksBtn.addEventListener('click', showBookmarks);
    darkModeBtn.addEventListener('click', toggleDarkMode);
    prevPageBtn.addEventListener('click', previousPage);
    nextPageBtn.addEventListener('click', nextPage);
    chapterSelect.addEventListener('change', handleChapterChange);
    bookmarkBtn.addEventListener('click', () => toggleBookmark(event, currentManga.id));
    
   
    viewButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            viewButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentView = btn.dataset.view;
            displayMangaGrid(currentView === 'bookmarks' ? getBookmarkedManga() : sampleManga);
        });
    });
    
    
    categoryTags.forEach(tag => {
        tag.addEventListener('click', () => {
            const genre = tag.textContent;
            const filteredManga = sampleManga.filter(manga => 
                manga.genre.includes(genre)
            );
            displayMangaGrid(filteredManga);
        });
    });
}


function handleChapterChange(e) {
    const chapterIndex = parseInt(e.target.value);
    if (!isNaN(chapterIndex)) {
        currentChapter = currentManga.chapters[chapterIndex];
        currentPage = 1;
        updateViewer();
    }
}


function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredManga = sampleManga.filter(manga => 
        manga.title.toLowerCase().includes(searchTerm) ||
        manga.genre.some(g => g.toLowerCase().includes(searchTerm)) ||
        manga.description.toLowerCase().includes(searchTerm)
    );
    displayMangaGrid(filteredManga);
}


function showHome() {
    showLoading();
    mangaViewer.style.display = 'none';
    mangaGrid.style.display = 'grid';
    displayMangaGrid(sampleManga);
    hideLoading();
}

function showBookmarks() {
    showLoading();
    displayMangaGrid(getBookmarkedManga());
    hideLoading();
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        updateViewer();
    }
}

function nextPage() {
    
    currentPage++;
    updateViewer();
}


function showLoading() {
    loadingOverlay.style.display = 'flex';
}

function hideLoading() {
    setTimeout(() => {
        loadingOverlay.style.display = 'none';
    }, 500); 
}


window.addEventListener('load', init); 