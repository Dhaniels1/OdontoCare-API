using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Security;
using OdontoCare.Application.Services;
using OdontoCare.Domain.Entities;
namespace OdontoCare.API.Controllers;
[ApiController, Route("api/orcamentos"), Authorize(Roles = Roles.Todos)]
public sealed class OrcamentosController(FinanceiroService service) : ControllerBase
{
    [HttpGet]
    public async Task<IActionResult> Listar() => Ok(ResultWrapper<List<OrcamentoResponseDto>>.Ok(await service.OrcamentosAsync()));
    [HttpGet("{id:int}")]
    public async Task<IActionResult> Buscar(int id) => await service.OrcamentoAsync(id) is { } item
        ? Ok(ResultWrapper<OrcamentoResponseDto>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpPost, Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Criar(OrcamentoGravarDto dto)
    {
        var item=(await service.GravarOrcamentoAsync(null,dto))!;
        return CreatedAtAction(nameof(Buscar), new { id=item.IdOrcamento }, ResultWrapper<OrcamentoResponseDto>.Ok(item));
    }
    [HttpPut("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Atualizar(int id, OrcamentoGravarDto dto) => await service.GravarOrcamentoAsync(id,dto) is { } item
        ? Ok(ResultWrapper<OrcamentoResponseDto>.Ok(item)) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
    [HttpDelete("{id:int}"), Authorize(Roles = Roles.Gestao)]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirOrcamentoAsync(id)
        ? Ok(ResultWrapper<object>.Ok(null,"Registro excluído.")) : NotFound(ResultWrapper<object>.NotFound("Registro não encontrado."));
}
