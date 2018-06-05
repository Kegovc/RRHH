import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {
  public title = 'RRHH';
  constructor() {
    console.log('home contructor');
   }

  ngOnInit() {
    console.log('home onInit');
  }

}
