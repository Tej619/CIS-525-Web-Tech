"use strict";
const getElement = (selector) => document.querySelector(selector);

// image constants
const backImgSrc = "images/back.png";
const blankImgSrc = "images/blank.png";
const cardImgSrcStart = "images/card_";

// game state
let cards = [];
let cardsArray = [];
let flipped = [];
let matched = [];
let attempts = 0;
let correct = 0;
let gameActive = true;

document.addEventListener("DOMContentLoaded", () => {
  // Load settings data
  loadSettings();

  // Display cards and player info
  displayCards();
  displayPlayerInfo();

  // Add click event handler for each card link
  addCardClickHandlers();

  // Add click event handler for each tab link button
  addTabClickHandlers();

  // Add click event handler for Save Settings button
  getElement("#save_settings").addEventListener("click", saveSettings);

  // Add click event handler for New Game link
  getElement("#new_game_link").addEventListener("click", (e) => {
    e.preventDefault();
    location.reload();
  });
});

function loadSettings() {
  const playerName = localStorage.getItem("playerName") || "";
  const numCards = localStorage.getItem("numCards") || "48";

  getElement("#player_name").value = playerName;
  getElement("#num_cards").value = numCards;

  createCardsArray(parseInt(numCards));
}

function getPlayerHighScore(playerName) {
  const scores = JSON.parse(localStorage.getItem("playerScores") || "{}");
  return scores[playerName] || "0";
}

function setPlayerHighScore(playerName, score) {
  const scores = JSON.parse(localStorage.getItem("playerScores") || "{}");
  if (!scores[playerName] || parseInt(score) > parseInt(scores[playerName])) {
    scores[playerName] = score;
    localStorage.setItem("playerScores", JSON.stringify(scores));
  }
}

function createCardsArray(numCards) {
  cardsArray = [];
  const pairsNeeded = numCards / 2;

  for (let i = 0; i < pairsNeeded; i++) {
    const cardNum = (i % 24) + 1;
    cardsArray.push(cardNum);
    cardsArray.push(cardNum);
  }

  // Shuffle the cards with random
  for (let i = cardsArray.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [cardsArray[i], cardsArray[j]] = [cardsArray[j], cardsArray[i]];
  }
}

function displayCards() {
  const cardsDiv = getElement("#cards");
  cardsDiv.innerHTML = "";

  const cardsPerRow = 8;
  const rows = Math.ceil(cardsArray.length / cardsPerRow);

  for (let row = 0; row < rows; row++) {
    const rowDiv = document.createElement("div");

    for (let col = 0; col < cardsPerRow; col++) {
      const index = row * cardsPerRow + col;
      if (index >= cardsArray.length) break;

      const cardNum = cardsArray[index];
      const link = document.createElement("a");
      link.href = "#";
      link.id = cardNum;
      link.dataset.index = index;

      const img = document.createElement("img");
      img.src = backImgSrc;
      img.alt = "";

      link.appendChild(img);
      rowDiv.appendChild(link);
    }

    cardsDiv.appendChild(rowDiv);
  }
}

function displayPlayerInfo() {
  const playerName = localStorage.getItem("playerName") || "Player";
  const highScore = getPlayerHighScore(playerName);

  getElement("#player").textContent = "Player: " + playerName;
  getElement("#high_score").textContent = "High Score: " + highScore;
}

function addCardClickHandlers() {
  const cardLinks = document.querySelectorAll("#cards a");
  cardLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      if (
        gameActive &&
        !flipped.includes(parseInt(link.dataset.index)) &&
        !matched.includes(parseInt(link.dataset.index))
      ) {
        flipCard(link);
      }
    });
  });
}

function flipCard(link) {
  const index = parseInt(link.dataset.index);
  const img = link.querySelector("img");

  if (flipped.includes(index)) return;

  flipped.push(index);

  // Fade effect
  fadeIn(img, cardImgSrcStart + cardsArray[index] + ".png");

  if (flipped.length === 2) {
    gameActive = false;
    setTimeout(checkMatch, 1000);
  }
}

function fadeIn(img, newSrc) {
  let opacity = 1;
  img.src = newSrc;

  const fadeInterval = setInterval(() => {
    opacity -= 0.05;
    if (opacity <= 0) {
      clearInterval(fadeInterval);
      img.style.opacity = 1;
    } else {
      img.style.opacity = opacity;
    }
  }, 20);
}

function checkMatch() {
  const index1 = flipped[0];
  const index2 = flipped[1];
  const card1Num = cardsArray[index1];
  const card2Num = cardsArray[index2];

  attempts++;

  const link1 = getElement(`a[data-index="${index1}"]`);
  const link2 = getElement(`a[data-index="${index2}"]`);
  const img1 = link1.querySelector("img");
  const img2 = link2.querySelector("img");

  if (card1Num === card2Num) {
    // Match found
    correct++;
    matched.push(index1);
    matched.push(index2);

    setTimeout(() => {
      img1.src = blankImgSrc;
      img2.src = blankImgSrc;
      link1.style.pointerEvents = "none";
      link2.style.pointerEvents = "none";

      if (matched.length === cardsArray.length) {
        endGame();
      } else {
        flipped = [];
        gameActive = true;
      }
    }, 500);
  } else {
    // No match
    setTimeout(() => {
      fadeIn(img1, backImgSrc);
      fadeIn(img2, backImgSrc);

      setTimeout(() => {
        flipped = [];
        gameActive = true;
      }, 100);
    }, 500);
  }
}

function endGame() {
  gameActive = false;
  const percentage = Math.round((correct / attempts) * 100);
  const playerName = localStorage.getItem("playerName") || "Player";
  const currentHighScore = parseInt(getPlayerHighScore(playerName));

  if (percentage > currentHighScore) {
    setPlayerHighScore(playerName, percentage);
    getElement("#high_score").textContent = "High Score: " + percentage;
  }

  getElement("#correct").textContent =
    "Correct: " + correct + "/" + attempts + " (" + percentage + "%)";
  getElement("#new_game").classList.remove("hide");
}

function saveSettings() {
  const playerName = getElement("#player_name").value || "Player";
  const numCards = getElement("#num_cards").value;

  localStorage.setItem("playerName", playerName);
  localStorage.setItem("numCards", numCards);

  location.reload();
}

function addTabClickHandlers() {
  const tabButtons = document.querySelectorAll(".tablinks");
  const tabContents = document.querySelectorAll(".tabcontent");

  tabButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      tabContents.forEach((content) => content.classList.add("hide"));

      tabButtons.forEach((btn) => btn.classList.remove("active"));

      const tabId = button.id.replace("_link", "");
      getElement("#" + tabId).classList.remove("hide");

      button.classList.add("active");
    });
  });
}
