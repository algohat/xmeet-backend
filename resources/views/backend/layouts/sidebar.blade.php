<div class="nk-sidebar">
    <div class="nk-nav-scroll">
        <ul class="metismenu" id="menu">

            <li class="nav-label" style="color:gray;">Dashboard</li>

            <li>
                <a href="{{ route('admin.dashboard') }}" aria-expanded="false">
                    <i class="fa fa-bar-chart menu-icon"></i><span class="nav-text">Dashboard</span>
                </a>
            </li>


            <li class="nav-label" style="color:gray;">Menu</li>


            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="icon-user menu-icon"></i><span class="nav-text">User Info</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('user.index')  }}">Users List</a></li>
                </ul>
            </li>


            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="icon-grid menu-icon"></i><span class="nav-text">Interests</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('interests.index')  }}">Interest Lists</a></li>
                </ul>
            </li>


            <li>
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fa fa-gift menu-icon"></i><span class="nav-text">Packages</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.packages.list')  }}">Package Lists</a></li>
                </ul>
            </li>


        </ul>
    </div>
</div>
