using OdontoCare.Application.Features.Consultas.DTOs;
using OdontoCare.Application.Features.Estoque.DTOs;
using System.ComponentModel.DataAnnotations;

namespace OdontoCare.Application.Features.Financeiro.DTOs;

public sealed record PainelDto(int TotalPacientes, int TotalConsultas, int TotalProcedimentos, int TotalPagamentos,
    int TotalMateriais, int ConsultasHoje, decimal ValorRecebido, decimal ValorPendente, decimal ValorCancelado,
    List<MaterialDto> MateriaisBaixoEstoque,
    List<ProcedimentoResumoDto> DadosProcedimentos, List<ConsultaListaDto> ProximasConsultas);
