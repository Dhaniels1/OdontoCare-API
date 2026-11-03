using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Security;
using OdontoCare.Application.Services;
using OdontoCare.Domain.Entities;
namespace OdontoCare.API.Controllers;
[ApiController, Route("api/procedimentos"), Authorize(Roles = Roles.Todos)]
public sealed class ProcedimentosController(FinanceiroService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> Listar() => Ok(ResultWrapper<List<Procedimento>>.Ok(await service.ProcedimentosAsync()));
    [HttpGet("{id:int}")]
    public async Task<IActionResult> Buscar(int id) => await service.ProcedimentoAsync(id) is { } item
        ? Ok(ResultWrapper<Procedimento>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpPost, Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Criar(ProcedimentoGravarDto dto)
    {
        var item=(await service.GravarProcedimentoAsync(null,dto))!;
        return CreatedAtAction(nameof(Buscar), new { id=item.IdProcedimento }, ResultWrapper<Procedimento>.Ok(item));
    }
    [HttpPut("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Atualizar(int id, ProcedimentoGravarDto dto) => await service.GravarProcedimentoAsync(id,dto) is { } item
        ? Ok(ResultWrapper<Procedimento>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpDelete("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirProcedimentoAsync(id)
        ? Ok(ResultWrapper<object>.Ok(null,"Registro excluído.")) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
}
