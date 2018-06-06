import { AuthService } from './../shared/services/auth.service';
import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-logoff',
  templateUrl: './logoff.component.html',
  styleUrls: ['./logoff.component.scss']
})
export class LogoffComponent implements OnInit {

  constructor(
    private authService: AuthService
  ) {
    authService.executeAccess('logoff');
  }

  ngOnInit() {
  }

}
