import { TestBed, inject } from '@angular/core/testing';

import { ReporteService } from './reporte.service';

describe('ReporteService', () => {
  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [ReporteService]
    });
  });

  it('should be created', inject([ReporteService], (service: ReporteService) => {
    expect(service).toBeTruthy();
  }));
});
