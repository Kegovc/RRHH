import { environment } from './../../environments/environment';
import { AuthService } from './../shared/services/auth.service';
import { Component, OnInit } from '@angular/core';
import { Router, Event, NavigationStart} from '@angular/router';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss']
})
export class NavbarComponent implements OnInit {

  public isCollapsed = true;
  public navItem = 'hola';
  public user: any = {};
  public urlSSO = environment.sso;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    router.events.subscribe( (event: Event) => {
      if (event instanceof NavigationStart) {
        this.urlActive(event.url);
      }
    });
    this.urlActive(window.location.pathname);
  }

  ngOnInit() {
    this.authService.getProfile()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (!response.fun.access) {
        this.authService.executeAccess(response.fun.execute);
      } else {
        this.user = response.fun.ls;
      }
    });
  }

  urlActive(url: string) {
    const arrayURL = url.split('/');
        if (arrayURL[1] === '') {
          arrayURL[1] = 'Inicio';
        }
        this.navItem = arrayURL[1];
  }
}
