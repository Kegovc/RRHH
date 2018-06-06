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
  public navitem = '';
  public user: any = {};

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    router.events.subscribe( (event: Event) => {
      if (event instanceof NavigationStart) {
        console.log(event.url);
        const arrayURL = event.url.split('/');
        console.log(arrayURL);
      }
    });
  }

  ngOnInit() {
    this.authService.getProfile()
    .then(response => {
      console.log(response);
      if (!response.fun.access) {
        this.authService.executeAccess(response.fun.execute);
      } else {
        this.user = response.fun.ls;
      }
    });
  }

}
