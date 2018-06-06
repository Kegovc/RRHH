import { AuthService } from './shared/services/auth.service';
import { Component } from '@angular/core';
import { environment } from '../environments/environment';
import { Router, Event, NavigationStart} from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent  {
  public loading = false;
  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    router.events.subscribe( (event: Event) => {
      if (event instanceof NavigationStart) {
        this.checkingPermissions();
        console.log(event.url);
      }
    });
  }


  checkingPermissions() {
    this.authService.haveAccess()
  .then(response => {
    console.log(response);
    if (!response.fun.access) {
      this.authService.executeAccess(response.fun.execute);
    } else {
      this.loading = true;
    }
  });
  }
}
