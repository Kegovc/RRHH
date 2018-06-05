import { AuthService } from './shared/services/auth.service';
import { Component, OnInit } from '@angular/core';
import { environment } from '../environments/environment';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent implements OnInit {
  public loading = false;

  constructor(
    private authService: AuthService
  ) {}
  ngOnInit() {
    this.checkingPermissions();
  }

  checkingPermissions() {
    this.authService.haveAccess()
  .then(response => {
    console.log(response);
    if (!response.fun.access) {
      switch (response.fun.execute) {
        case 'toSSO': {
          // window.location.href = environment.sso;
          break;
        }
        case 'logoff': {
          // window.location.href = `${environment.sso}close`;
          break;
        }
      }
    } else {
      this.loading = true;
    }
  });
  }
}
