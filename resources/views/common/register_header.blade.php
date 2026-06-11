@if(Session::has('user_id'))
<script>$("body").removeClass("unregister");</script>
@endif
<!-- Page header top -->
<nav class="navbar navbar-default navbar-xs top_nav">
    <div class="container">
        <div class="navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-left">
                <li class="top_bar_logo">
                    <a href="{{ url('login') }}">
                        <img src="{{ asset('assets/front_end/images/logo.png') }}" alt="Betogram">
                    </a>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right navbar-actions">
                <li class="header_search">
                    <form role="search">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="search" class="form-control" placeholder="Search for matches..." name="SearchMatches" id="SearchMatchesInput" onkeyup="SearchMatches(this.value)" autocomplete="off">
                            <div id="search-results-dropdown" class="search-results-dropdown" style="display:none;"></div>
                        </div>
                    </form>
                </li>
                @if(Session::has('user_id'))
                <li class="top_icon">
                    <a href="#" aria-label="Notifications">
                        <img src="{{ asset('assets/front_end/images/notification.png') }}" alt="Notifications">
                    </a>
                </li>
                <li class="top_icon">
                    <a href="#" aria-label="Messages">
                        <img src="{{ asset('assets/front_end/images/messages.png') }}" alt="Messages">
                    </a>
                </li>
                <li class="dropdown profile_drop">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <img src="{{ asset('assets/front_end/images/settings.png') }}" alt="Account settings">
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#"><i class="fa fa-tachometer" aria-hidden="true"></i> User Dashboard</a></li>
                        <li><a href="#"><i class="fa fa-pencil" aria-hidden="true"></i> Edit Profile</a></li>
                        <li><a href="{{ url('change-password') }}"><i class="fa fa-key" aria-hidden="true"></i> Change Password</a></li>
                        <li><a href="{{ url('logout') }}"><i class="fa fa-sign-out" aria-hidden="true"></i> Logout</a></li>
                    </ul>
                </li>
                <li class="header_deposit hidden-xs">
                    <a href="{{ url('payment-page') }}" class="btn btn-deposit btn-account-balance" title="Go to deposit page" aria-label="Deposit funds">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                        <span class="account-balance-text">${{ number_format(Auth::user()->balance ?? 0, 2) }}</span>
                    </a>
                </li>
                <li class="header_wallet hidden-xs">
                    <a href="{{ url('my-wallet') }}" class="btn btn-wallet" title="My Wallet" aria-label="Wallet">
                        <i class="fa fa-wallet" aria-hidden="true"></i>
                        <span class="wallet-text">Wallet</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<style>
    .btn-wallet {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 25px;
        padding: 8px 20px;
        margin-left: 10px;
        transition: all 0.3s;
    }

    .btn-wallet:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-deposit {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 25px;
        padding: 8px 20px;
        transition: all 0.3s;
    }

    .btn-deposit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        color: white;
    }

    .account-balance-text {
        font-weight: bold;
        margin-left: 8px;
    }

    .wallet-text {
        margin-left: 8px;
    }
</style>

<div class="nav_hight"></div>
<!-- Page header end -->

<script>
var allMatches = [];
var sports = ['football', 'hockey', 'basketball', 'boxing', 'american-football'];

// Load all fixtures from all sports on page load
$(document).ready(function() {
    var loadedCount = 0;
    
    sports.forEach(function(sport) {
        $.ajax({
            type: "GET",
            url: "{{ url('api/fixtures') }}?sport=" + sport + "&status=all",
            success: function(result) {
                if (result.data) {
                    result.data.forEach(function(match) {
                        match.sport = sport;
                        allMatches.push(match);
                    });
                } else if (Array.isArray(result)) {
                    result.forEach(function(match) {
                        match.sport = sport;
                        allMatches.push(match);
                    });
                }
            }
        });
    });
});

function SearchMatches(searchTerm) {
    var dropdown = $('#search-results-dropdown');
    
    if (searchTerm === '' || searchTerm.length < 1) {
        dropdown.hide();
        return;
    }
    
    searchTerm = searchTerm.toLowerCase();
    var filtered = allMatches.filter(function(match) {
        var homeTeam = match.home_team ? match.home_team.toLowerCase() : '';
        var awayTeam = match.away_team ? match.away_team.toLowerCase() : '';
        return homeTeam.includes(searchTerm) || awayTeam.includes(searchTerm);
    });
    
    if (filtered.length > 0) {
        var html = '<ul class="search-results-list">';
        filtered.slice(0, 5).forEach(function(match) {
            var matchText = (match.home_team || 'Match') + ' vs ' + (match.away_team || '');
            var sportLabel = match.sport ? ' (' + match.sport.charAt(0).toUpperCase() + match.sport.slice(1) + ')' : '';
            var sportUrl = match.sport ? "{{ url('') }}/" + match.sport : '#';
            html += '<li><a href="' + sportUrl + '?fixture=' + match.id + '" class="search-result-item" onclick="scrollToMatch(' + match.id + '); return true;">' + matchText + sportLabel + '</a></li>';
        });
        html += '</ul>';
        dropdown.html(html).show();
    } else {
        dropdown.html('<div class="search-no-results">No matches found</div>').show();
    }
}

function scrollToMatch(fixtureId) {
    // Store fixture ID in session storage to highlight on sport page
    sessionStorage.setItem('highlightFixture', fixtureId);
}

$(document).on('click', function(e) {
    if (!$(e.target).closest('.header_search').length) {
        $('#search-results-dropdown').hide();
    }
});
</script>