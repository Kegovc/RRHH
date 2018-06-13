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
  public loading = true;
  public data: any = {};
  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    router.events.subscribe( (event: Event) => {
      if (event instanceof NavigationStart) {
        this.checkingPermissions();
      }
    });
  }


  checkingPermissions() {
   this.authService.haveAccess()
  .then((response: any) => {
    if (environment.debug) { console.log(response); }
    if (!response.fun.access) {
      this.authService.executeAccess(response.fun.execute);
    } else {
      this.loading = true;
    }
  });
  }

}
