import { AuthService } from './auth.service';
import { environment } from './../../../environments/environment';
import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class VacacionesService {

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) { }

  getInfo(data) {
    data.accessToken = this.authService.getToken();
    return this.http.post(`${environment.api}get_info_vacaciones`, data)
    .toPromise();
  }
}
