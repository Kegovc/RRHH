import { AuthService } from './../shared/services/auth.service';
import { CatalogoService } from './catalogo.service';
import { Component, OnInit } from '@angular/core';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-catalogos',
  templateUrl: './catalogos.component.html',
  styleUrls: ['./catalogos.component.scss']
})
export class CatalogosComponent implements OnInit {
  public load = false;
  public data: any = {};
  public empleados: any[] = [];
  public selectedCatalogoId: number;
  public carga =  false;

  constructor(
    private catalogoService: CatalogoService,
    private authService: AuthService
  ) {
    this.selectedCatalogoId = 0;
    this.catalogoService.getCatalogo()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.empleados = response.fun.ls;
        if (environment.debug) { console.log(this.empleados); }
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    });
   }

  loadCatalogo(id) {
    console.log(id);
   }
  ngOnInit() {
  }

}
