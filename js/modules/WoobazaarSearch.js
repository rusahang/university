import $ from 'jquery';

class WoobazaarSearch {
    // 1. describe and creat/initiate our object
    constructor() {
        this.addSearchHTML();
        this.resultsDiv = $("#search-overlay__results");
        this.openButton =       $(".js-search-trigger");
        this.closeButton =      $(".search-overlay__close");
        this.searchOverlay =    $(".search-overlay");
        this.woobazaarSearchField = $("#search-term");
        this.events();
        this.isOverlayOpen = false;
        this.isSpinnerVisible = false;
        this.previousValue;
        this.typingTimer;
    }
    // 2. events
    events() {
        this.openButton.on("click", this.openOverlay.bind(this));
        this.closeButton.on("click", this.closeOverlay.bind(this));
        $(document).on("keydown", this.keyPressDispatcher.bind(this));
        this.woobazaarSearchField.on("keyup", this.typingLogic.bind(this));
    }
    
    
    // 3. methods (function, action ...)
    typingLogic() {
        if(this.woobazaarSearchField.val() != this.previousValue) {
            clearTimeout(this.typingTimer);
            
            if(this.woobazaarSearchField.val()) {
                if(!this.isSpinnerVisible) {
            this.resultsDiv.html('<div class="spinner-loader"></div>');
            this.isSpinnerVisible = true;
        }
        this.typingTimer = setTimeout(this.getResults.bind(this), 750);
            } else {
                this.resultsDiv.html('');
                this.isSpinnerVisible = false;
            }
            
        }
        
        this.previousValue = this.woobazaarSearchField.val();
    }
    
    getResults() {
        $.getJSON(woobazaarData.root_url + '/wp-json/woobazaar/v1/search?term=' + this.woobazaarSearchField.val(), (results) => {
            this.resultsDiv.html(`
            <div class="row">
            <div class="one-third">
            <h2 class="search-overlay__section-title">Products</h2>
            ${results.product.length ? '<ul class="professor-cards">' : `<p>Sorry, your search did not match any product <a href="${woobazaarData.root_url}/shop">View all products</a></p>`}
              ${results.product.map(item => `
                <li class="professor-card__list-item">
                  <a class="professor-card" href="${item.permalink}">
                    <img class="professor-card__image" src="${item.image}">
                    <span class="professor-card__name">${item.title}</span>
                  </a>
                </li>
              `).join('')}
            ${results.product.length ? '</ul>' : ''}
            </div>
          <div class="one-third">
            <h2 class="search-overlay__section-title">General Information</h2>
            ${results.generalInfo.length ? '<ul class="link-list min-list">' : '<p>No general information matches that search.</p>'}
              ${results.generalInfo.map(item => `<li><a href="${item.permalink}">${item.title}</a> ${item.postType == 'post' ? `by ${item.authorName}` : ''}</li>`).join('')}
            ${results.generalInfo.length ? '</ul>' : ''}
          </div>
          <div class="one-third">
            <h2 class="search-overlay__section-title">Programs</h2>
            ${results.programs.length ? '<ul class="link-list min-list">' : `<p>Sorry, your search did not match any program <a href="${woobazaarData.root_url}/programs">View all programs</a></p>`}
              ${results.programs.map(item => `<li><a href="${item.permalink}">${item.title}</a></li>`).join('')}
            ${results.programs.length ? '</ul>' : ''}

          </div>
          <div class="one-third">
            <h2 class="search-overlay__section-title">Campuses</h2>
            ${results.campuses.length ? '<ul class="link-list min-list">' : `<p>Sorry, your search did not match any campus <a href="${woobazaarData.root_url}/campuses">View all campuses</a></p>`}
              ${results.campuses.map(item => `<li><a href="${item.permalink}">${item.title}</a></li>`).join('')}
            ${results.campuses.length ? '</ul>' : ''}

            <h2 class="search-overlay__section-title">Events</h2>
            ${results.events.length ? '' : `<p>Sorry, your search did not match any event <a href="${woobazaarData.root_url}/events">View all events</a></p>`}
              ${results.events.map(item => `
                <div class="event-summary">
                  <a class="event-summary__date t-center" href="${item.permalink}">
                    <span class="event-summary__month">${item.month}</span>
                    <span class="event-summary__day">${item.day}</span>  
                  </a>
                  <div class="event-summary__content">
                    <h5 class="event-summary__title headline headline--tiny"><a href="${item.permalink}">${item.title}</a></h5>
                    <p>${item.description} <a href="${item.permalink}" class="nu gray">Learn more</a></p>
                  </div>
                </div>
              `).join('')}
            
          <div class="one-third">
          <h2 class="search-overlay__section-title">Professors</h2>
            ${results.professors.length ? '<ul class="professor-cards">' : `<p>No professors match that search.</p>`}
              ${results.professors.map(item => `
                <li class="professor-card__list-item">
                  <a class="professor-card" href="${item.permalink}">
                    <img class="professor-card__image" src="${item.image}">
                    <span class="professor-card__name">${item.title}</span>
                  </a>
                </li>
              `).join('')}
            ${results.professors.length ? '</ul>' : ''}
         </div>

          </div>
        </div>
      `);
      this.isSpinnerVisible = false;
            
        });
  }
    
    keyPressDispatcher(fonebay) {
        if (fonebay.keyCode == 83 && !this.isOverlayOpen && !$("input, textarea").is(':focus')) {
            this.openOverlay();
        }
        
        if (fonebay.keyCode == 27 && this.isOverlayOpen) {
            this.closeOverlay();
        }
    }
    openOverlay() {
        this.searchOverlay.addClass("search-overlay--active");
        $("body").addClass("body-no-scroll");
        setTimeout(() => this.woobazaarSearchField.focus(), 301);
        this.woobazaarSearchField.val('');
        console.log("Our open method just ran!");
        this.isOverlayOpen = true;
        return false;
    }
    closeOverlay() {
        this.searchOverlay.removeClass("search-overlay--active");
        $("body").removeClass("body-no-scroll");
        console.log("Our close method just ran!");
        this.isOverlayOpen = false;
    }
    
    addSearchHTML() {
        $("body").append(`
        <div class="search-overlay">
    <div class="search-overlay__top">
        <div class="container">
            <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
            <input type="text" class="search-term" placeholder="What are you looking for?" id="search-term">
            <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
        </div>
    </div>
    <div class="container">
        <div id="search-overlay__results">
        </div>
    </div>
</div>
    `);
    }
}

export default WoobazaarSearch;