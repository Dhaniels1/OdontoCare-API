using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Security;
using OdontoCare.Application.Interfaces;
namespace OdontoCare.API.Controllers;
[ApiController, Route("api/pagamentos"), Authorize(Roles = Roles.Todos)]
public sealed class PagamentosController(IPagamentoService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> Listar([FromQuery] int? consultaId) => Ok(ResultWrapper<List<PagamentoResponseDto>>.Ok(await service.ListarAsync(consultaId)));
    [HttpGet("{id:int}")]
    public async Task<IActionResult> Buscar(int id) => await service.BuscarAsync(id) is { } item
        ? Ok(ResultWrapper<PagamentoResponseDto>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpPost, Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Criar(PagamentoGravarDto dto)
    {
        var item=(await service.GravarAsync(null,dto))!;
        return CreatedAtAction(nameof(Buscar), new { id=item.IdPagamento }, ResultWrapper<PagamentoResponseDto>.Ok(item));
    }
    [HttpPut("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Atualizar(int id, PagamentoGravarDto dto) => await service.GravarAsync(id,dto) is { } item
        ? Ok(ResultWrapper<PagamentoResponseDto>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpDelete("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirAsync(id)
        ? Ok(ResultWrapper<object>.Ok(null,"Registro excluído.")) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
}
