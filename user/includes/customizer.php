<!-- APP CUSTOMIZER -->
<div id="app-customizer" class="app-customizer">
  <!-- Toggle button to open/close the customizer -->
  <a href="javascript:void(0)" 
     class="app-customizer-toggle theme-color" 
     data-toggle="class" 
     data-class="open"
     data-active="false"
     data-target="#app-customizer">
    <i class="fa fa-gear"></i> <!-- Gear icon for the toggle button -->
  </a>

  <!-- Customizer tabs for Menubar and Navbar settings -->
  <div class="customizer-tabs">
    <!-- Tabs list -->
    <ul class="nav nav-tabs" role="tablist">
      <!-- Menubar Customizer Tab -->
      <li role="presentation" class="active">
        <a href="#menubar-customizer" aria-controls="menubar-customizer" role="tab" data-toggle="tab">Menubar</a>
      </li>
      <!-- Navbar Customizer Tab -->
      <li role="presentation">
        <a href="#navbar-customizer" aria-controls="navbar-customizer" role="tab" data-toggle="tab">Navbar</a>
      </li>
    </ul><!-- .nav-tabs -->

    <!-- Tab content -->
    <div class="tab-content">
      <!-- Menubar Customizer Tab Content -->
      <div role="tabpanel" class="tab-pane in active fade" id="menubar-customizer">
        <!-- Fold Menubar Switch -->
        <div class="hidden-menubar-top hidden-float">
          <div class="m-b-0">
            <label for="menubar-fold-switch">Fold Menubar</label>
            <div class="pull-right">
              <!-- Switch for folding the menubar -->
              <input id="menubar-fold-switch" type="checkbox" data-switchery data-size="small" />
            </div>
          </div>
          <hr class="m-h-md">
        </div>

        <!-- Menubar Theme Options -->
        <div class="radio radio-default m-b-md">
          <!-- Light Theme Option -->
          <input type="radio" id="menubar-light-theme" name="menubar-theme" data-toggle="menubar-theme" data-theme="light">
          <label for="menubar-light-theme">Light</label>
        </div>

        <div class="radio radio-inverse m-b-md">
          <!-- Dark Theme Option -->
          <input type="radio" id="menubar-dark-theme" name="menubar-theme" data-toggle="menubar-theme" data-theme="dark">
          <label for="menubar-dark-theme">Dark</label>
        </div>
      </div><!-- .tab-pane -->

      <!-- Navbar Customizer Tab Content -->
      <div role="tabpanel" class="tab-pane fade" id="navbar-customizer">
        <!-- This section is populated automatically by JavaScript -->
      </div><!-- .tab-pane -->
    </div><!-- .tab-content -->
  </div><!-- .customizer-tabs -->
  <hr class="m-0">
</div><!-- #app-customizer -->